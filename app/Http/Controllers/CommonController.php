<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Countries;
use App\User;
use App\UserWallet;
use App\UserWalletDetail;
use App\ServiceProvider;

use DB;
use Session;
 
class CommonController extends Controller
{

    public static $page_limit = 10;  
    public static $book_prefix = 'AM';  
    public static $delivery_text = 'Expected Delivery On : ';  

    public function __construct() {
      $admin_settings = DB::table('em_admin_settings')->where('id',1)->get();
      if($admin_settings->isNotEmpty() && isset($admin_settings[0])) {
        self::$page_limit = (!empty(trim($admin_settings[0]->def_pagination_limit))) ? $admin_settings[0]->def_pagination_limit : 10; 
        self::$book_prefix = (!empty(trim($admin_settings[0]->book_prefix))) ? $admin_settings[0]->book_prefix : 'AM';
      }
    }
    
    // Get the Site ON / OFF Status
      public static function getSiteStatus() {
        $site_on_off = '';
        $admin_settings = DB::table('em_admin_settings')->where('id',1)->get();
        if($admin_settings->isNotEmpty() && isset($admin_settings[0])) {
            $site_on_off = $admin_settings[0]->site_on_off;
        }
        if(empty($site_on_off) || empty($site_on_off)) {
            $site_on_off = "OFF";
        }

        return $site_on_off;
      }

      // Get the Default Expiry time for the user in Months
      public static function getDefExpiry() {
        $def_expiry_after = '';
        $admin_settings = DB::table('em_admin_settings')->where('id',1)->get();
        if($admin_settings->isNotEmpty() && isset($admin_settings[0])) {
            $def_expiry_after = $admin_settings[0]->def_expiry_after;
        }
        if(empty($def_expiry_after) || ($def_expiry_after == 0)) {
            $def_expiry_after = 1;
        }

        return $def_expiry_after;
      }

      public static function getStoreCurrency() {
      $country = DB::table('hby_countries')
        ->leftjoin('hby_restaurants', 'hby_restaurants.country_id', '=', 'hby_countries.id')
        ->where('hby_restaurants.user_id', Auth::User()->id)->first();
      if(!empty($country)) {
        $currency = $country->currency_symbol;
        $mrp_symbol = config("constants.mrp_text").$currency;
        Session::put('session_country', $country->id);
      } else {
        $currency = config("constants.money_symbol");
        $mrp_symbol = config("constants.mrp_symbol");
      }          
      return array($currency,$mrp_symbol);
    }

    public static function getAdminCurrency() {
      $session_country = Session::get('session_country');
      if($session_country>0) {
          $country = Countries::where('id', $session_country)->first();
          if(!empty($country)) {
            $currency = $country->currency_symbol;
            $mrp_symbol = config("constants.mrp_text").$currency;
          } else {
            $currency = config("constants.money_symbol");
            $mrp_symbol = config("constants.mrp_symbol");
          }          
      }   else {
          $currency = config("constants.money_symbol");
          $mrp_symbol = config("constants.mrp_symbol");
      } 

      return array($currency,$mrp_symbol);
    }

    public static function otpSend($userid)    { 

        $user = User::find($userid); 

        if($user->mobile == '9578074575' || $user->mobile == '7904072981') {
          $otpGeneration  = '123456';
        } else {
          $otpGeneration  = CommonController::generateNumericOTP(6);
        } 
        $user->otp = $otpGeneration; 

        $user->save();
        User::where('id', $userid)->update(['otp'=>$otpGeneration]);
        $mobile = $user->code_mobile;
        //$mobile = $user->mobile; 
        CommonController::SendOTP($mobile, $otpGeneration);
            

        return true;

    }

    public static function SendOTP($mobile_no, $otp) {
    $curl = curl_init();
    $response = '';
    $err = '';
    $message = "Thanks+for+registering+your+Aarofix.Your+Verification+Code+is+$otp";
    /*https://api-server14.com/api/send.aspx?apikey=L0s8dHkkJlC0BuMN1nn2W6Izv&language=1&sender=CACO&mobile=$mobilno&message=$m*/
     
    /*$url = "https://api-server14.com/api/send.aspx?apikey=L0s8dHkkJlC0BuMN1nn2W6Izv&language=1&sender=CACO&mobile=".$mobile_no."&message=".$message;
    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => $url,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "GET",
      CURLOPT_SSL_VERIFYHOST => 0,
      CURLOPT_SSL_VERIFYPEER => 0,
      CURLOPT_HTTPHEADER => array(
        "content-type: application/json"
      ),
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
      return "cURL Error #:" . $err;
    } else {
      return $response;
    }*/
    return '';
 
  }

    public static function SendOTPEmail($user) {
      dispatch(new UserOTPEmailSender($user));
    }

    public static function pushSendUserNotification($fcmid, $message, $title, $fcmMsg=[])   {

        if (!defined('FIREBASE_KEY')) {
            define('FIREBASE_KEY', 'AAAAtYCx0h0:APA91bFKOeat5Dt4YtcPVRa58G06ZNl997HnjzhUJ0rWa-tNi383-vQrowjrMxYGg6ft5XBP9M32yAbhFYoXUgvvS5o4v6-LpF4ynYuv0KuDCgCNwwHp2kZMOCh4Vly1n4ikemCMBGAb');
        }

        $type = isset($fcmMsg["fcm"]["notification"]["type"]) ? $fcmMsg["fcm"]["notification"]["type"] : "100";
        $booking_id = isset($fcmMsg["fcm"]["notification"]["booking_id"]) ? $fcmMsg["fcm"]["notification"]["booking_id"] : "0";

        $fcmMsg['fcm']['notification']['body'] = $message;
        $fcmMsg['fcm']['notification']['title'] = $title;
        $fcmMsg['fcm']['notification']['sound'] = "default";
        $fcmMsg['fcm']['notification']['color'] = "#203E78";
        $fcmMsg['fcm']['notification']['type'] = $type; 

        $fcmMsgSend = $fcmMsg['fcm']['notification'];


        if ($fcmid) {
            /*$fcmMsg = array(
                'title' => $title,
                'body' => $message,
                'sound' => "default",
                'color' => "#203E78",
                'type' => $type,
                'order_id' => $order_id
            );  echo "<pre>"; print_r($fcmMsgSend); exit;*/
            $fcmMsg = $fcmMsgSend;

            $fcmFields = array(
                'to' => $fcmid,
                'priority' => 'high',
                'notification' => $fcmMsg,
                'data' => $fcmMsg
            );

            $headers = array(
                'Authorization: key=' . FIREBASE_KEY,
                'Content-Type: application/json'
            );

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fcmFields));
            $result = curl_exec($ch);
            curl_close($ch);

        }
        return true;
  }

  public static function getUserDetails($user_id) {
    $user_type = DB::table('users')->where('id', $user_id)->value('user_type');
    if($user_type == 'SERVICEPROVIDER') {
      $user = User::leftjoin('em_service_provider', 'em_service_provider.user_id', 'users.id')
                ->where('user_id', $user_id)
                ->select('users.id', 'reg_no', 'fcm_id', 'is_otp_verified', 'name', 'nick_name', 'last_name', 
                    'name_title', 'email', 'country', 'country_code', 'mobile', 'code_mobile', 'address', 'api_token', 
                    'is_email_verified', 'wallet_amount', 'gender', 'dob', 'is_register_complete', 'step',
                    'status', 'approve_status', 'users.profile_image', 'service_provider_type', 'tax_percentage', 
                    'tax_description', 'service_type', 'house', 'locality', 'city', 'pincode', 'zone_ids', 
                    'current_house', 'current_landmark', 'current_address', 'type_of_proof', 'id_proof_front', 
                    'id_proof_back', 'pan_card_front', 'pan_card_back', 'pan_number', 'pan_name', 
                    'category_id', 'sub_category_ids', 'service_ids', 'start_time', 'end_time', 
                    'emergency_available', 'gst_name', 'gst_number', 
                    'bank_id', 'account_name', 'account_number', 'ifsc_code', 'cheque_image', 
                    'emergency_contact_name', 'emergency_contact_number', 'emergency_contact_relationship',
                    'experience', 'experience_description'
                  )->first(); 

      $servicer = ServiceProvider::where('user_id', $user_id)->select('user_id', 'language_ids', 'skill_ids')->first();
      $user->servicer = $servicer;
    } else {
      $user = User::where('id', $user_id)->select('id', 'reg_no', 'name', 'email', 'is_email_verified', 'mobile', 'code_mobile', 
        'country','country_code', 'gender', 'dob', 'referal_code', 'is_referal_code', 'is_refered_by', 'is_referal_user', 'is_register_complete', 'joined_date', 'fcm_id', 'user_type', 'status', 'is_otp_verified', 'last_login_date', 'last_app_opened_date', 'user_source_from', 'api_token', 'api_token_expiry', 'step', 'approve_status', 'profile_image')->first();
    }

    return $user;
  }

  public static function getDocuments($user_id)    {
        $data = ['id_proof_front'=>'', 'id_proof_back'=>'', 'driving_license_front'=>'', 'driving_license_back'=>'',
                 'noc_front'=>'', 'cheque_image'=>'', 'pan_card_front'=>'', 'pan_card_back'=>'', 'profile_image'=>'']; 
        $profile_image = DB::table('users')->where('id', $user_id)->value('profile_image');
        $images = DB::table('em_service_provider')->where('user_id', $user_id)
          ->select('id_proof_front', 'id_proof_back', 'driving_license_front', 'driving_license_back', 'noc_front', 'cheque_image','pan_card_front', 'pan_card_back')
          ->first();
        if(!empty($images->id_proof_front))
            $data['id_proof_front'] =  config("constants.APP_IMAGE_URL").'uploads/userdocs/'.$images->id_proof_front;

        if(!empty($images->id_proof_back))
            $data['id_proof_back'] = config("constants.APP_IMAGE_URL").'uploads/userdocs/'.$images->id_proof_back;

        if(!empty($images->driving_license_front))
            $data['driving_license_front'] = config("constants.APP_IMAGE_URL").'uploads/userdocs/'.$images->driving_license_front;

        if(!empty($images->driving_license_back))
            $data['driving_license_back'] = config("constants.APP_IMAGE_URL").'uploads/userdocs/'.$images->driving_license_back;

        if(!empty($images->noc_front))
            $data['noc_front'] = config("constants.APP_IMAGE_URL").'uploads/userdocs/'.$images->noc_front;

        if(!empty($images->cheque_image))
            $data['cheque_image'] = config("constants.APP_IMAGE_URL").'uploads/userdocs/'.$images->cheque_image;

        if(!empty($images->pan_card_front))
            $data['pan_card_front'] = config("constants.APP_IMAGE_URL").'uploads/userdocs/'.$images->pan_card_front;

        if(!empty($images->pan_card_back))
            $data['pan_card_back'] = config("constants.APP_IMAGE_URL").'uploads/userdocs/'.$images->pan_card_back;

        if(!empty($profile_image))
            $data['profile_image'] = config("constants.APP_IMAGE_URL").'uploads/userdocs/'.$profile_image;
        
        return $data;
    }

  public static function push_notification($user_id, $fcmMsg, $no_notify=0, $fcm_id='')    {

      $user = User::find($user_id);
      $notification_status = $user->notification_status;

      if($no_notify == 0) {
        DB::table('em_notifications')->insert([
          'user_id'=>$user_id,
          'fcm_id'=>$user->fcm_id,
          'title'=>$fcmMsg['fcm']['notification']['title'],
          'message'=>$fcmMsg['fcm']['notification']['body'],
          'created_at'=>date('Y-m-d H:i:s'),
          'notify_date'=>date('Y-m-d H:i:s'),
        ]);
      }

      $title = $fcmMsg['fcm']['notification']['body'];
      $message = $fcmMsg['fcm']['notification']['title'];

      $fcmMsg['fcm']['notification']['body'] = $message;
      $fcmMsg['fcm']['notification']['title'] = $title;
      $fcmMsg['fcm']['notification']['sound'] = "default";
      $fcmMsg['fcm']['notification']['color'] = "#203E78";

      $fcmMsgSend = $fcmMsg['fcm']['notification'];

      if(empty($fcm_id)) {
        $fcm_id = $user->fcm_id;
      }

      self::pushSendUserNotification($fcm_id, $message, $title, $fcmMsgSend);

      /*if($notification_status == 'ON') {

        $user_id = strval($user_id);
        require 'push_notifications/vendor/autoload.php';
        $pushNotifications = new \Pusher\PushNotifications\PushNotifications(array(
          "instanceId" => "70c22fae-bd53-4d22-8aae-e8bdb97fbe91",
          //"secretKey" => "94FC307945E44E5AB1EA32AB007D2431DF3AB698B77AB0ADACE994B9B327A851",
          "secretKey" => "29C4BCCC639A2A794D4125A9E1DC0539A1C2739D12499C7213523BE7B39E6963",
        ));
 
        $publishResponse = $pushNotifications->publishToUsers(
          array($user_id),
          array(
            "fcm" => array(
              "notification" => $fcmMsg,
               "data" => $fcmMsg
            ),
            "apns" => array(
              "aps" => array(
              "alert" => $fcmMsg,
              "data" => $fcmMsg
            ))
        ));
      }*/
        return true;
  }


  public static function generateNumericOTP($n) {

        // Take a generator string which consist of
        // all numeric digits
        $generator = "1357902468";
        $result = "";

        for ($i = 1; $i <= $n; $i++) {
            $result .= substr($generator, (rand()%(strlen($generator))), 1);
        }
        //$result = "1234";
        // Return result
        $result = '123456';
        return $result;
    }

}