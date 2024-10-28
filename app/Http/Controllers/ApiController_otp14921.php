<?php

namespace App\Http\Controllers;

use App\User;
use App\ServiceProvider;
use App\Countries;
use App\Languages;
use App\UserAddress;
use App\Category;
use App\SubCategory;
use App\Banner;
use App\Services;
use App\SubServices;
use App\Cart;
use App\CartItem;
use App\Zones;
use App\Banks;
use App\Slots;
use App\Booking;
use App\JobStatus;
use App\UserCartSubServices;
use App\ServicerServiceDetails;
use App\BookingSubServices;
use App\UserCartServices;

use App\Http\Controllers\CommonController;

use Illuminate\Http\Request;
use DB;
use DateTime;

class ApiController extends Controller
{

	public function __construct()    {
       // try{ 
            $site_on_off = CommonController::getSiteStatus();
            if($site_on_off != "ON") {
                echo json_encode(['status' => 3, 'data' => [], 'message' => "Under Maintenance"]);
                exit;
            }
       /* }   catch(\Throwable $th) {
            echo json_encode(['status' => 0, 'data' => [], 'message' => $th->getMessage()]);
            exit;
        }  */
    }
    
    public function getCountries() {
        try{  

            $countries = Countries::where('status', 'ACTIVE')->orderby('position', 'asc')->get();
            if($countries->isNotEmpty()) {
                return response()->json(['status' => 1, 'data' => $countries, 'message' => 'Countries List']);
            }   else {
                return response()->json(['status' => 0, 'data' => null, 'message' => 'No Countries List']);
            }
            
        }   catch(\Throwable $th) {
            return response()->json(['status' => 0, 'data' => null, 'message' => $th->getMessage()]);
        }
        
    }

    /* getAllSlots - List of all Active Slots added by Admin  */
    public function getAllSlots(Request $request) {
        try {   
            $inputJSON = file_get_contents('php://input');

            $input = json_decode($inputJSON, TRUE);

            $requiredParams = ['user_id', 'api_token'];

            $error = $this->checkParams($input, $requiredParams, $request);

            if(empty($error)) {

                $userid = ((isset($input) && isset($input['user_id']))) ? $input['user_id'] : 0; 
                $page_no = ((isset($input) && isset($input['page_no']))) ? $input['page_no'] : 0; 

                $api_token = $request->header('x-api-key');
                $limit = CommonController::$page_limit;

                $mes = User::checkTokenExpiry($userid, $api_token);
                $status = $mes['status'];   $message = $mes['message'];
                if($status != 1) {
                    return response()->json([ 'status' => $status, 'data' => [], 'message' => $message]);
                }   else {
                    if($userid > 0) { 

                        $slots = Slots::WHERE('status', 'ACTIVE')->orderby('position', 'ASC')->get();
                        if($slots->isNotEmpty()) { 
                            return response()->json([ 'status' => 1, 'data' => $slots, 'message' => "Slots List"]);
                        }   else {
                            return response()->json([ 'status' => 0, 'data' => [], 'message' => "No Slots"]);
                        }  
                    }   else {
                        return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid Inputs']);
                    }
                }
            }    else {
                return response()->json([ 'status' => 0, 'data' => [], 'message' => $error]);
            }
        }   catch(\Throwable $th) {
            return response()->json(['status' => 0, 'data' => [], 'message' => $th->getMessage()]);
        }  
    } 


    /* To check all the mandatory parameters are given as the input for the api call
    Fn Name: checkParams
    return: Error Info / empty
    */
    public function checkParams($input = [], $requiredParams = [], $request = [], $isform=false) {
        $error = ''; 

        if($isform) {
            $input = $request->all();
        }

        if(empty($input) || empty($requiredParams)) {
            $error = "Please input all the required parameters ds";
            return $error;
        }
       // echo "<pre>"; print_r($input); print_r($requiredParams); exit;
        if(count($input)>0 && count($requiredParams)>0) {
            foreach($requiredParams as $key=>$value) {
                if($value == 'api_token') {
                    $api_token = $request->header('x-api-key');
                    if(empty($api_token)) {
                        $error .= ' Api key' . ', ';
                    }
                } else if(!isset($input[$value])) {
                    $error .= $value . ', ';
                }
            }
            if(!empty($error)) {
                $error .= ' parameters missing in input';
            }
        }   else {
            $error = "Please input all the required parameters";
        }
        return $error;
    }

    /* To Check is Country code is Valid
    Fn Name: checkValidCountryCode
    return: empty / error message
    */
    public function checkValidCountryCode($country_code) {
        try{  
            $error = '';
            $countries = Countries::where('status', 'ACTIVE')->where('phonecode', $country_code)
                ->select('id')
                ->get();
            if($countries->isNotEmpty()) {
                return $error;
            }   else {
                $error = 'Invalid Country Code';
                return $error;
            }
        }   catch(\Throwable $th) {
            return response()->json(['status' => 0, 'data' => null, 'message' => $th->getMessage()]);
        } 
    }

    /* Send OTP to the User
    Fn Name: sendOtp
    return: Success Message / Failure Message
    */
    public function sendOtp(Request $request)     {

        try {
            $inputJSON = file_get_contents('php://input');

            $input = json_decode($inputJSON, TRUE);

            $requiredParams = ['code', 'cell'];

            $error = $this->checkParams($input, $requiredParams);

            if(empty($error)) {
                $country_code = $input['code'];
                $chkcountrycode = $this->checkValidCountryCode($country_code);

                if(!empty($chkcountrycode)) {
                    return response()->json([ 'status' => 0, 'message' => $chkcountrycode]);
                }

                $mobile = $input['cell'];

                /*$mobileregex = "/^[6-9][0-9]{9}$/";
                if (!isset($input["cell"]) || empty($input["cell"]) || preg_match($mobileregex, $input["cell"]) == 0) {
                    $errorData = null;
                    $errorData["data"] = array("status" => 0,   "message" => "Phone number is invalid");
                    echo json_encode($errorData);

                    die();
                }*/
                $otp = CommonController::generateNumericOTP(4);

                DB::table('em_otp')->where('cell', $mobile)->delete();
                DB::table('em_otp')->insert([
                    'otp' => $otp,
                    'country_code' => $country_code,
                    'cell' => $mobile,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
  
                return response()->json([ 'status' => 1, 'data' => null, 'message' => "OTP sent successfully"]);
                    
            }   else {
                return response()->json([ 'status' => 0, 'data' => [], 'message' => $error]);
            }
        }   catch(\Throwable $th) {
            return response()->json(['status' => 0, 'data' => [], 'message' => $th->getMessage()]);
        }   
    }

    /* New User Registration / Login
    Fn Name: postUser
    return: Success Message with the Registered User info / Failure Message
    */
    public function postUser(Request $request)     {

        try {
            $inputJSON = file_get_contents('php://input');

            $input = json_decode($inputJSON, TRUE);

            $requiredParams = ['otp', 'country_code', 'mobile', 'device_type', 'device_id', 'fcm_token'];

            $error = $this->checkParams($input, $requiredParams);

            if(empty($error)) {
                $country_code = $input['country_code'];
                $otp = isset($input['otp']) ? $input['otp'] : '';

                $chkcountrycode = $this->checkValidCountryCode($country_code);

                if(!empty($chkcountrycode)) {
                    return response()->json([ 'status' => 0, 'message' => $chkcountrycode]);
                }

                $country = Countries::where('status', 'ACTIVE')->where('phonecode', $country_code)->value('id');

                $mobile = $input['mobile'];
                $device_type = $input['device_type'];
                $device_id = $input['device_id'];
                $fcm_id = $input['fcm_token']; 

                // Mobile number must not be start with 0
                if(substr( $mobile, 0, 1 ) === "0") {
                    return response()->json(['status' => 0, 'data' => [], 'message' => 'Invalid Mobile']);
                }

                $mobileEx = DB::table('users')->where('mobile', $mobile)->where('user_type', 'USER')->first();
                if(!empty($mobileEx)) {  // registered user
                    if($mobileEx->status != 'ACTIVE') {
                        return response()->json(['status' => 0, 'data' => [], 'message' => 'Your Account has been Blocked']);
                    }   elseif($mobileEx->user_type != 'USER') {
                        return response()->json(['status' => 0, 'data' => [], 'message' => 'Mobile Number Already Exists']);
                    } else {
                        $date = date('Y-m-d H:i:s');
                        DB::table('users')->where('fcm_id', $fcm_id)->update(['fcm_id' => '']);
                        // Check for Expiry
                        $expiry = $mobileEx->api_token_expiry;

                        $user = User::where('id', $mobileEx->id)->where('user_type', 'USER')->get();
                        if($user->isNotEmpty()) {
                            $user = $user[0];
                        }

                        //if($expiry <= date('Y-m-d H:i:s')) {

                            $def_expiry_after =  CommonController::getDefExpiry();
                            $user->api_token_expiry = date('Y-m-d H:i:s', strtotime('+'.$def_expiry_after.' months'. $date));
                            //$user->api_token = User::random_strings(30);
                            $user->save();
                        //}
                        
                        $ex_fcm_id = $user->fcm_id;

                        if(empty($otp)) {
                            CommonController::otpSend($mobileEx->id);
                            $user->is_otp_verified = 0;
                        }   else {
                            $user->otp = $otp;
                            $user->is_otp_verified = 1;
                        }
                        
                        $user->fcm_id = $fcm_id;
                        $user->last_login_date = date('Y-m-d H:i:s');
                        $user->last_app_opened_date = date('Y-m-d H:i:s');
                        $user->save();

                        /* Check and update and logout the session if current login from different device */

                        $atotherdevice = DB::table('em_users_loginstatus')
                            ->where('user_id', $mobileEx->id)
                            ->orderby('id', 'desc')
                            ->first();

                        if(!empty($atotherdevice)) {
                            $ex_device_id = $atotherdevice->device_id;
                            if($ex_device_id != $device_id) {
                                /* Send notification to the previous device of the user */
                                $user->api_token = User::random_strings(30);
                                $user->save();

                                $title = ' Login on Different Device ';
                                $message = 'Last login is on Different Device. So Logout and Login Again.';
                                $fcmMsg = array("fcm" => array("notification" => array(
                                    "title" => $title,
                                    "body" => $message,
                                    "type" => "1",
                                  )));

                                CommonController::push_notification($user->id, $fcmMsg, 0, $ex_fcm_id);
                            }
                        }

                        $atotherdevice = DB::table('em_users_loginstatus')
                            ->where('user_id', $mobileEx->id)
                            ->where('device_id', '!=', $device_id)
                            ->count();

                        if($atotherdevice > 0) {
                            DB::table('em_users_loginstatus')
                            ->where('user_id', $mobileEx->id)
                            ->where('device_id', '!=', $device_id)
                            ->update(['api_token_expiry'=> $mobileEx->api_token_expiry, 'updated_at'=>date('Y-m-d H:i:s')]);
                        }

                        DB::table('em_users_loginstatus')->insert([
                            'user_id' => $mobileEx->id,
                            'fcm_id' => $fcm_id,
                            'device_id' => $device_id,
                            'device_type' => $device_type,
                            'api_token_expiry' => $mobileEx->api_token_expiry,
                            'created_at' => date('Y-m-d H:i:s'),
                        ]);
                        $user = User::find($mobileEx->id);
                        $is_register_complete = $user->is_register_complete;

                       // CommonController::auth($mobileEx->id);

                        $user = CommonController::getUserDetails($user->id);

                        DB::table('em_otp')->where('otp', $otp)->where('cell', $mobile)
                            ->where('country_code', $country_code)->delete(); 
                        if($is_register_complete == 0) {
                            return response()->json(['status' => 2, 'message' => 'Login Successful. Update Profile', 'data' => $user]);
                        }   else {
                            return response()->json(['status' => 1, 'message' => 'Login Successful.', 'data' => $user]);
                        }
                    }
                }   else {  // new user

                    $today = date('ymd');
                    $fircheck_qry = "SELECT reg_no FROM users WHERE reg_no LIKE '$today%' ORDER BY id DESC LIMIT 1";
                    $fircheck = DB::select($fircheck_qry); 
                    if(is_array($fircheck) && count($fircheck) > 0) {
                        $reg_no = $fircheck[0]->reg_no;
                        $user_reg_no = $reg_no + 1;
                    }   else {
                        $user_reg_no = $today . '0001';
                    } 

                    $date = date('Y-m-d H:i:s');
                    $user = new User();
                    $user->reg_no = $user_reg_no;
                    $user->mobile = $mobile;
                    $user->country = $country;
                    $user->country_code = $country_code;
                    $user->code_mobile = $country_code.$mobile;
                    $user->fcm_id = $fcm_id;
                    $user->user_type = 'USER';
                    $user->status = 'ACTIVE';
                    $user->otp = $otp;
                    $referral_code = User::random_strings(5);
                    $user->referal_code = $referral_code;
                    $user->joined_date = date('Y-m-d H:i:s');

                    $user->last_login_date = $date;
                    $user->last_app_opened_date = $date;
                    $user->user_source_from = $device_type;
                    $user->api_token = User::random_strings(30);

                    $def_expiry_after =  CommonController::getDefExpiry();

                    $user->api_token_expiry = date('Y-m-d H:i:s', strtotime('+'.$def_expiry_after.' months'. $date));
                    $user->created_at = $date;
                    $user->referral_code = User::random_strings(5);
                    $user->wallet_amount = 0;
                    $user->gender = 'MALE';
                    $user->save();
                    
                    $userid = $user->id;

                    if(empty($otp)) {
                        $user->is_otp_verified = 0;
                        CommonController::otpSend($user->id);
                    }   else {
                        $user->otp = $otp;
                        $user->is_otp_verified = 1;
                    }

                    $user->save();

                    DB::table('em_users_loginstatus')->insert([
                        'user_id' => $user->id,
                        'fcm_id' => $fcm_id,
                        'device_id' => $device_id,
                        'device_type' => $device_type,
                        'api_token_expiry' => $user->api_token_expiry,
                        'status' => 'LOGIN',
                        'created_at' => date('Y-m-d H:i:s'),
                    ]);

                    DB::table('users_active_status')->insert([
                        'user_id' => $user->id,
                        'status' => 'ACTIVE',
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);                    

                    $user = CommonController::getUserDetails($user->id);

                    DB::table('em_otp')->where('otp', $otp)->where('cell', $mobile)
                            ->where('country_code', $country_code)->delete(); 

                    if (!empty($user)) {

                        return response()->json(['status' => 2, 'message' => 'Successfully Registered.', 'data' => $user]);

                    } else {

                        return response()->json(['status' => 0, 'data' => [], 'message' => 'Registration Failed']);
                    }
                }                
            }   else {
                return response()->json([ 'status' => 0, 'data' => [], 'message' => $error]);
            }
        }   catch(\Throwable $th) {
            return response()->json(['status' => 0, 'data' => [], 'message' => $th->getMessage()]);
        }   
    }

    /* Mobile Number Verification using OTP
    Fn Name: otpVerification
    return: Success Message with the User info / Failure Message
    */
    public function otpVerification(Request $request)    {

        try {
            $inputJSON = file_get_contents('php://input');

            $input = json_decode($inputJSON, TRUE);

            $requiredParams = ['user_id', 'otp', 'api_token'];

            $error = $this->checkParams($input, $requiredParams, $request);

            if(empty($error)) {

                $userid = $input['user_id'];

                $otp = $input['otp'];

                $api_token = $request->header('x-api-key');

                $user = User::where('id', $userid)->where('api_token', $api_token)->limit(1)->get();

                if($user->isNotEmpty()) {
                    if(isset($user[0])) {
                        $user = $user[0];
                        if($user->otp == $otp) {
                            $user->is_otp_verified = 1;

                            $user->save();

                            $user = CommonController::getUserDetails($user->id);

                            return response()->json(['status' => 1, 'message' => 'Your mobile number verified', 'data' => $user]);
                        }   else {
                            return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid OTP']);
                        }
                    }   else {
                        return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid Details']);
                    }
                    
                }   else {
                    $error = 'Invalid Token';
                    return response()->json([ 'status' => 0, 'data' => [], 'message' => $error]);
                }
            }    else {
                return response()->json([ 'status' => 0, 'data' => [], 'message' => $error]);
            }
        }   catch(\Throwable $th) {
            return response()->json(['status' => 0, 'data' => [], 'message' => $th->getMessage()]);
        } 
    }


    /* Mobile Number Verification using OTP
    Fn Name: otpVerification
    return: Success Message with the User info / Failure Message
    */
    public function otpVerification_old(Request $request)    {
        try {
            $inputJSON = file_get_contents('php://input');

            $input = json_decode($inputJSON, TRUE);

            //$requiredParams = ['user_id', 'otp', 'api_token'];

            $requiredParams = ['mobile', 'otp'];

            $error = $this->checkParams($input, $requiredParams, $request);

            if(empty($error)) {

                $mobile = $input['mobile'];

                $otp = $input['otp'];

                $type = isset($input['type']) ?  $input['type'] : 'mobile';

                if(empty(trim($type))) {
                    $type = 'mobile';
                }

                $emailPattern = '/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/';
                $mobilePattern = "/^[0-9]{10}$/";

                $inputtype = "email";
                $inputvalue = $mobile;
                if (preg_match($emailPattern, $mobile)) {
                    $inputtype = "email";
                    $inputvalue = $mobile;
                } else if (preg_match($mobilePattern, $mobile)) {
                    $inputtype = "mobile";
                    $inputvalue = $mobile;
                } else {
                    return response()->json([
                        'status' => 0,
                        'message' => "Invalid Input",
                        "data" => null
                    ]);
                }

              //  $api_token = $request->header('x-api-key');

                // $user = User::where('id', $userid)->where('api_token', $api_token)->limit(1)->get();
                /*$inputtype = "mobile";
                $inputvalue = $mobile;*/

                $user = User::where($inputtype, $inputvalue)->where('user_type', 'USER')->limit(1)->get();

                if($user->isNotEmpty()) {
                    if(isset($user[0])) {
                        $user = $user[0];
                        if($inputtype == 'mobile') {
                            //(new TwilioController())->verify($userid, $otp);
                            //if($user->otp == $otp) {
                                $user->status = 'ACTIVE';

                                $user->is_otp_verified = 1;

                                $user->save();

                                $user = CommonController::getUserDetails($user->id);

                                if(empty($user->name) || empty($user->email)) {
                                    return response()->json(['status' => 2, 'message' => 'update Profile', 'data' => $user]);
                                }
                                
                                return response()->json(['status' => 1, 'message' => 'Your mobile number verified', 'data' => $user]);
                            /*}   else {
                                return response()->json([ 'status' => 0, 'data' => null, 'message' => 'Invalid OTP']);
                            }*/
                        }   else {
                            if($user->email_otp == $otp) {
                                $user->status = 'ACTIVE';

                                $user->is_email_verified = 1;

                                $user->save();

                                $user = CommonController::getUserDetails($user->id);

                                return response()->json(['status' => 1, 'message' => 'Your email verified', 'data' => $user]);
                            }   else {
                                return response()->json([ 'status' => 0, 'data' => null, 'message' => 'Invalid OTP']);
                            }
                        } 

                        $user = CommonController::getUserDetails($user->id);
                        return response()->json(['status' => 1, 'message' => 'Your mobile number verified', 'data' => $user]);
                    }   else {
                        return response()->json([ 'status' => 0, 'data' => null, 'message' => 'Invalid Details']);
                    }
                    
                }   else {
                    $error = 'Invalid User';
                    return response()->json([ 'status' => 0, 'data' => null, 'message' => $error]);
                }
            }    else {
                return response()->json([ 'status' => 0, 'data' => null, 'message' => $error]);
            }
        }   catch(\Throwable $th) {
            return response()->json(['status' => 0, 'data' => null, 'message' => $th->getMessage()]);
        } 
    }

    /*  Resend OTP
    Fn Name: resendOtp
    return: Success Message with the User info and the OTP sent again / Failure Message
    */
    public function resendOtp(Request $request)   {

        try {
            $inputJSON = file_get_contents('php://input');

            $input = json_decode($inputJSON, TRUE);

            $requiredParams = ['user_id', 'api_token'];

            $error = $this->checkParams($input, $requiredParams, $request);

            if(empty($error)) {
                $userid = $input['user_id'];

                $api_token = $request->header('x-api-key');

                $user = User::where('id', $userid)->where('api_token', $api_token)->limit(1)->get();
                if($user->isNotEmpty()) {
                    if(isset($user[0])) {
                        $user = $user[0];
                        CommonController::otpSend($user->id);
                        $user = CommonController::getUserDetails($user->id);
                        return response()->json(['status' => 1, 'message' => 'OTP Sent Again', 'data' => $user]);
                    }   else {
                        return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid Details']);
                    }
                }  else {
                    return response()->json(['status' => 0, 'message' => 'Invalid User / Token']);
                }
            }    else {
                return response()->json([ 'status' => 0, 'data' => [], 'message' => $error]);
            }
        }   catch(\Throwable $th) {
            return response()->json(['status' => 0, 'data' => [], 'message' => $th->getMessage()]);
        }  
    }

    /* Update User Details */
    public function postUpdateUser(Request $request)
    {

        try {
            $inputJSON = file_get_contents('php://input');

            $input = json_decode($inputJSON, TRUE);

            //$input = $request->all(); 

            $requiredParams = ['user_id', 'api_token', 'mobile']; //, 'email', 'name', 'profile_image', 'country_id'

            $error = $this->checkParams($input, $requiredParams, $request);

            if(empty($error)) {

                $name = (isset($input['name'])) ? $input['name'] : '';

                $email = (isset($input['email'])) ? $input['email'] : ''; 

                $gender = (isset($input['gender'])) ? $input['gender'] : '';

                $dob = (isset($input['dob'])) ? $input['dob'] : '';

                $userid = $input['user_id'];

                $mobile = $input['mobile'];

                $country_id = (isset($input['country_id'])) ? $input['country_id'] : '';

                $api_token = $request->header('x-api-key');

                $mes = User::checkTokenExpiry($userid, $api_token);
                $status = $mes['status'];   $message = $mes['message'];
                if($status != 1) {
                    return response()->json([ 'status' => $status, 'data' => null, 'message' => $message]);
                }   else {

                    $emailEx = DB::table('users')->where('email', $email)
                        ->where('user_type', 'USER')
                        ->whereNotIn('id', [$userid])->first();

                    $dummy = null;

                    if (!empty($emailEx)) {

                        return response()->json([

                            'status' => 0,
                            'message' => "Email exists",
                            'data' => $dummy,
                        ]);
                    } 

                    if(!empty(trim($mobile))) {
                        $mobileEx = DB::table('users')->where('mobile', $mobile)
                            ->where('user_type', 'USER')
                            ->whereNotIn('id', [$userid])->first();

                        if (!empty($mobileEx)) {

                            return response()->json([

                                'status' => 0,
                                'message' => "Mobile exists",
                                'data' => $dummy,
                            ]);
                        }
                    }

                   /* $is_refered_by = 0;
                    $user_referred = '';
                    if(!empty($referal_code)) {
                        $user_referred = User::where('referal_code', $referal_code)
                            ->where('id', '!=', $userid)->value('id');
                        if($user_referred > 0) {
                            $is_refered_by = 1;
                        }   else {
                            return response()->json([

                                'status' => 0,
                                'message' => "Invalid Referal Code",
                                'data' => $dummy,
                            ]);
                        }
                    }

                    $is_referal_code = $referal_code;  

                    $is_referal_user = $user_referred;

                    $is_register_complete = 1;          */

                    $user = User::find($userid);
                    $exMobile = $user->mobile;  
                    if(!empty($country_id) && !empty($mobile)) {
                        $country_code = Countries::where('id', $country_id)->value('phonecode');

                        $user->country_code = $country_code;

                        $user->mobile = $mobile;

                        $user->code_mobile = $country_code.$mobile;
                    }

                    $user->name = $name;

                    $user->email = $email;   

                   /* $user->is_refered_by = $is_refered_by;

                    $user->is_referal_code = $is_referal_code;

                    $user->is_referal_user = $is_referal_user;

                    $user->step = $is_register_complete;*/

                    $user->is_register_complete = 1;

                    $user->gender = $gender;

                    if(!empty($dob)) {
                        $dob = date('Y-m-d', strtotime($dob));
                        $user->dob = $dob;
                    }                    

                    /*$profile_image = $request->profile_image;

                    if (!empty($profile_image)) {
                        
                        $imageName = rand(10,100).time(). '.' . $profile_image->getClientOriginalExtension();
                        $destinationPath = public_path('/uploads/profileimage');
                        $profile_image->move($destinationPath, $imageName);

                        $user->profile_image = $imageName;
                        
                    }*/

                    $user->save();


                    if ($exMobile != $mobile) {

                        CommonController::otpSend($user->id); 
  
                        $user->is_otp_verified = 0;

                        $user->save();

                        $user = CommonController::getUserDetails($user->id);

                        return response()->json(['status' => 1, 'data' => $user, 'message' => 'Mobile Verification is pending.']);

                    }

                    $user = CommonController::getUserDetails($user->id);
                    if (!empty($user)) {

                        return response()->json(['status' => 1, 'data' => $user, 'message' => 'Your details has been updated']);

                    } else {

                        return response()->json(['status' => 0, 'data' => null, 'message' => 'Something went to wrong']);
                    }
                }
            }    else {
                return response()->json([ 'status' => 0, 'data' => null, 'message' => $error]);
            }
        }   catch(\Throwable $th) {
            return response()->json(['status' => 0, 'data' => null, 'message' => $th->getMessage()]);
        } 

    }

    /* Update users Profile Image */
    public function postUpdateUserImage(Request $request)   {

        try {   
            $input = $request->all();

            $requiredParams = ['user_id', 'api_token'];

            $error = $this->checkParams($input, $requiredParams, $request, true);

            if(empty($error)) {
                $userid = $input['user_id'];
                $api_token = $request->header('x-api-key');
                $mes = User::checkTokenExpiry($userid, $api_token);
                $status = $mes['status'];   $message = $mes['message'];
                if($status != 1) {
                    return response()->json([ 'status' => $status, 'data' => [], 'message' => $message]);
                }   else {
                    $user = User::find($userid);
                    /*  Profile image of the User */
                    $accepted_formats = ['jpeg', 'jpg', 'png'];
                    $image = $request->file('profile_image');
                    if (!empty($image) && $image != 'null') {
                        $ext = $image->getClientOriginalExtension();
                        if(!in_array($ext, $accepted_formats)) {
                            return response()->json(['status' => 0, 'message' => 'File Format Wrong.Please upload PNG,JPEG,JPG']);
                        }
          
                        $spdocsImage = 'spdocs-' .rand().time() . '.' . $image->getClientOriginalExtension();

                        $destinationPath = public_path('/uploads/userdocs');

                        $image->move($destinationPath, $spdocsImage);

                        $user->profile_image =  $spdocsImage;   

                        $user->save();
                    }

                    $user = CommonController::getUserDetails($userid);
                    if(!empty($user)) {
                        return response()->json(['status' => 1, 'message' => 'User Details', 'data' => $user]);
                    }   else {
                        return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid User']);
                    }
                    
                }
            }    else {
                return response()->json([ 'status' => 0, 'data' => [], 'message' => $error]);
            }
        }   catch(\Throwable $th) {
            return response()->json(['status' => 0, 'data' => [], 'message' => $th->getMessage()]);
        }  
    }

    /*  Save New Address for the User
    Fn Name: postUserAddress
    return: Success Message / Failure Message
    */
    public function postUserAddress(Request $request)   {

        try {
            $inputJSON = file_get_contents('php://input');

            $input = json_decode($inputJSON, TRUE);

            $requiredParams = ['user_id', 'api_token', 'latitude', 'longitude', 'address', 'flatno', 'area', 'city',
             'pincode', 'country', 'zone_id'];

            $error = $this->checkParams($input, $requiredParams, $request);

            if(empty($error)) {
                $userid = $input['user_id'];
                $api_token = $request->header('x-api-key'); 
                $latitude = $input['latitude'];
                $longitude = $input['longitude'];
                $address = $input['address'];
                $flatno = $input['flatno'];
                $area = $input['area'];
                $city = $input['city'];
                $pincode = $input['pincode'];
                $country = $input['country'];
                $zone_id = $input['zone_id'];

                if(empty(trim($pincode))) {
                    return response()->json([ 'status' => 0, 'data' => [], 'message' => "Pincode must not be Empty"]);
                }

                $user_address_id = (isset($input['user_address_id'])) ? $input['user_address_id'] : 0; 
                $mes = User::checkTokenExpiry($userid, $api_token);
                $status = $mes['status'];   $message = $mes['message'];
                if($status != 1) {
                    return response()->json([ 'status' => $status, 'data' => [], 'message' => $message]);
                }   else {

                    $is_default = 0;
                    $exaddress = UserAddress::where('user_id', $userid)->count();
                    if($exaddress == 0) {
                        $is_default = 1;
                    }   

                    if($user_address_id > 0) {
                        $useraddress = UserAddress::find($user_address_id);
                        if(empty($useraddress)) {
                            return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid Users Address']);
                        }
                    }   else {
                        $useraddress = new UserAddress();
                    }

                    //$zone = Zones::where('zone_name', 'like', '%'.$pincode.'%')->skip(0)->limit(1)->get();
                    $zone = Zones::where('id', $zone_id)->where('status', 'ACTIVE')->get();
                    if($zone->isNotEmpty()) {
                        $zone = $zone[0];
                        $zone_id = $zone->id;
                    }

                    $user_name = User::where('id', $userid)->value('name');
                    $useraddress->code = date('YmdHis');
                    $useraddress->user_name = $user_name;
                    $useraddress->user_id = $userid;
                    $useraddress->zone_id = $zone_id;
                    $useraddress->address = $address;
                    $useraddress->latitude = $latitude;
                    $useraddress->longitude = $longitude;
                    $useraddress->flatno = $flatno;
                    $useraddress->pinarea = $area;
                    $useraddress->city = $city;
                    $useraddress->pin_code = $pincode;
                    $useraddress->country = $country;
                    $useraddress->is_default = $is_default;
                    $useraddress->status = 'ACTIVE';
                    $useraddress->created_by = $userid;
                    $useraddress->created_at = date('Y-m-d H:i:s');

                    $useraddress->save();

                    $useraddresses = UserAddress::where('user_id', $userid)->where('status', 'ACTIVE')
                        ->orderby('is_default', 'desc')->get();

                    if($useraddresses->isNotEmpty()) {
                        return response()->json(['status' => 1, 'message' => 'User Address Saved Successfully', 'data' => $useraddresses]);
                    }   else {
                        return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Empty Users Addresses']);
                    }
                }
            }    else {
                return response()->json([ 'status' => 0, 'data' => [], 'message' => $error]);
            }
        }   catch(\Throwable $th) {
            return response()->json(['status' => 0, 'data' => [], 'message' => $th->getMessage()]);
        }  
    }

    /*  Delete the Address for the User
    Fn Name: deleteUserAddress
    return: Success Message / Failure Message
    */
    public function deleteUserAddress(Request $request)   {

        try {
            $inputJSON = file_get_contents('php://input');

            $input = json_decode($inputJSON, TRUE);

            $requiredParams = ['user_id', 'api_token', 'user_address_id'];

            $error = $this->checkParams($input, $requiredParams, $request);

            if(empty($error)) {
                $userid = $input['user_id'];
                $api_token = $request->header('x-api-key');
                $user_address_id = $input['user_address_id'];
                $mes = User::checkTokenExpiry($userid, $api_token);
                $status = $mes['status'];   $message = $mes['message'];
                if($status != 1) {
                    return response()->json([ 'status' => $status, 'data' => [], 'message' => $message]);
                }   else {
                    if($user_address_id > 0) {
                        $useraddress = UserAddress::find($user_address_id);
                        if(!empty($useraddress)){

                            if($useraddress->is_default == 1) {
                                return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Default Users Address Cannot be Deleted']);
                            }

                            $useraddress->status = 'INACTIVE'; 
                            $useraddress->updated_by = $userid; 
                            $useraddress->updated_at = date('Y-m-d H:i:s');
                            $useraddress->save();

                            $useraddresses = UserAddress::where('user_id', $userid)->where('status', 'ACTIVE')->get();

                            if($useraddresses->isNotEmpty()) {
                                return response()->json(['status' => 1, 'message' => 'User Address Deleted Successfully', 'data' => $useraddresses]);
                            }   else {
                                return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Empty Users Addresses']);
                            }

                        }   else {
                            return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid User Address Id']);
                        }
                    }   else {
                        return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid User Address']);
                    }
                }
            }    else {
                return response()->json([ 'status' => 0, 'data' => [], 'message' => $error]);
            }
        }   catch(\Throwable $th) {
            return response()->json(['status' => 0, 'data' => [], 'message' => $th->getMessage()]);
        }  
    }

    /*  Make Dafault the Address for the User
    Fn Name: defaultUserAddress
    return: Success Message / Failure Message
    */
    public function defaultUserAddress(Request $request)   {

        try {
            $inputJSON = file_get_contents('php://input');

            $input = json_decode($inputJSON, TRUE);

            $requiredParams = ['user_id', 'api_token', 'user_address_id'];

            $error = $this->checkParams($input, $requiredParams, $request);

            if(empty($error)) {
                $userid = $input['user_id'];
                $api_token = $request->header('x-api-key');
                $user_address_id = $input['user_address_id'];
                $mes = User::checkTokenExpiry($userid, $api_token);
                $status = $mes['status'];   $message = $mes['message'];
                if($status != 1) {
                    return response()->json([ 'status' => $status, 'data' => [], 'message' => $message]);
                }   else {
                    if($user_address_id > 0) {
                        $useraddress = UserAddress::find($user_address_id);
                        if(!empty($useraddress)){

                            DB::table('users_address')->where('user_id', $userid)->update(['is_default'=>0]);

                            DB::table('users_address')->where('user_id', $userid)
                                ->where('id', $user_address_id)->update(['is_default'=>1, 'updated_by' => $userid,  'updated_at'=>date('Y-m-d H:i:s')]);
/*
                            $useraddress->is_default = 1;
                            $useraddress->updated_by = $userid;
                            $useraddress->updated_at = date('Y-m-d H:i:s');
                            $useraddress->save();*/

                            $useraddresses = UserAddress::where('user_id', $userid)->where('status', 'ACTIVE')->get();

                            if($useraddresses->isNotEmpty()) {
                                return response()->json(['status' => 1, 'message' => 'Default User Address saved Successfully', 'data' => $useraddresses]);
                            }   else {
                                return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Empty Users Addresses']);
                            }

                        }   else {
                            return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid User Address Id']);
                        }
                    }   else {
                        return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid User Address']);
                    }
                }
            }    else {
                return response()->json([ 'status' => 0, 'data' => [], 'message' => $error]);
            }
        }   catch(\Throwable $th) {
            return response()->json(['status' => 0, 'data' => [], 'message' => $th->getMessage()]);
        }  
    }

    /*  Select all the Addresses for the User
    Fn Name: getUserAddress
    return: Success Message with All the User Addresses List / Failure Message
    */
    public function getUserAddress(Request $request)   {

        try {   
            $inputJSON = file_get_contents('php://input');

            $input = json_decode($inputJSON, TRUE);

            $requiredParams = ['user_id', 'api_token'];

            $error = $this->checkParams($input, $requiredParams, $request);

            if(empty($error)) {
                $userid = $input['user_id'];
                $api_token = $request->header('x-api-key');
                $mes = User::checkTokenExpiry($userid, $api_token);
                $status = $mes['status'];   $message = $mes['message'];
                if($status != 1) {
                    return response()->json([ 'status' => $status, 'data' => [], 'message' => $message]);
                }   else {
                    
                    $useraddresses = UserAddress::where('user_id', $userid)->where('status', 'ACTIVE')->get();
                    if($useraddresses->isNotEmpty()) {
                        return response()->json(['status' => 1, 'message' => 'User Addresses List', 'data' => $useraddresses]);
                    }   else {
                        return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Empty Users Addresses']);
                    }
                    
                }
            }    else {
                return response()->json([ 'status' => 0, 'data' => [], 'message' => $error]);
            }
        }   catch(\Throwable $th) {
            return response()->json(['status' => 0, 'data' => [], 'message' => $th->getMessage()]);
        }  
    }

    /* Banners List
    Fn Name: getBanners
    return: Banners list / error
    */
    public function getBanners() {
        try{    
            $inputJSON = file_get_contents('php://input');
            $input = json_decode($inputJSON, TRUE); 
            $user_id = (isset($input['user_id'])) ? $input['user_id'] : 0;
   
            $banners_qry = Banner::where('status', 'ACTIVE') 
                ->select('id', 'banner_image', 'banner_type', 'link_type', 'link_id', 'position');
                if(count($vendors_arr)>1) {
                    $banners_qry->whereIN('link_id', $vendors_arr);
                }
                $banners = $banners_qry->orderby('position', 'ASC')
                ->get();
        
            
            if($banners->isNotEmpty()) {
                return response()->json(['status' => 1, 'data' => $banners, 'message' => 'Banners List']);
            }   else {
                return response()->json(['status' => 0, 'data' => [], 'message' => 'No Banners List']);
            }
            
        }   catch(\Throwable $th) {
            return response()->json(['status' => 0, 'data' => [], 'message' => $th->getMessage()]);
        }        
    }

    /* Get Home Page Contents */
    public function getHomeContents(Request $request) {
        try {   
            $inputJSON = file_get_contents('php://input');

            $input = json_decode($inputJSON, TRUE);

            $requiredParams = ['user_id', 'api_token'];

            $error = $this->checkParams($input, $requiredParams, $request);

            if(empty($error)) {

                $userid = ((isset($input) && isset($input['user_id']))) ? $input['user_id'] : 0; 
                
                $api_token = $request->header('x-api-key');
                $page_no = 0;  $limit = CommonController::$page_limit;

                $mes = User::checkTokenExpiry($userid, $api_token);
                $status = $mes['status'];   $message = $mes['message'];
                if($status != 1) {
                    return response()->json([ 'status' => $status, 'data' => [], 'message' => $message]);
                }   else {
                    if($userid > 0) { 
                        $bannerstop = $bannersmiddle = $bannersbottom = $categories = $subcategories = '';
                        $defaultuseraddress = '';
                        //DB::enableQueryLog();
                        $bannerstop = Banner::where('status', 'ACTIVE')->where('position', 'TOP')->orderby('id', 'DESC')->get();
                        $bannersmiddle = Banner::where('status', 'ACTIVE')->where('position', 'CENTER')->orderby('id', 'DESC')->get();
                        $bannersbottom = Banner::where('status', 'ACTIVE')->where('position', 'BOTTOM')->orderby('id', 'DESC')->get();

                        $where['home_display'] = 'YES';
                        $categories = Category::where($where)->where('status', 'ACTIVE')
                            //->select('id', 'id as homeCategoryId', 'name', 'name as homeName', 'type as homeType', 'image')
                            ->orderby('position', 'asc')->skip($page_no)->take(2)->get();

                        /*$subcategories = SubCategory::leftjoin('em_category', 'em_category.id', '=', 'em_sub_category.category_id')
                            ->where('em_sub_category.status', 'ACTIVE') 
                            ->select('em_sub_category.*', 'em_category.name as category_name')
                            //->select('em_sub_category.id as mainCategoryId', 'em_sub_category.name as title', 'em_sub_category.image', 'em_category.name as category_name')
                            ->orderby('em_sub_category.position', 'asc')
                            ->skip($page_no)->take(12)
                            ->get();*/

                        $subcategories = Category::where($where)->where('status', 'ACTIVE')
                            //->select('id', 'id as homeCategoryId', 'name', 'name as homeName', 'type as homeType', 'image')
                            ->orderby('position', 'asc')->skip($page_no)->take(21)->get();

                        $defaultuseraddress = UserAddress::where('user_id', $userid)
                            ->where('status', 'ACTIVE')
                            ->where('is_default', '1')->get();
  
                        $data = [];
                        //$query = DB::getQueryLog();          echo "<pre>"; print_r($query); 
                        if($bannerstop->isNotEmpty()) {
                            $data[] = ["title"=>"topbanner", "type"=>"banner", "values"=>$bannerstop];
                        }

                        if($categories->isNotEmpty()) {
                            $data[] = ["title"=>'categories', "type"=>"categories", "values"=>$categories];
                        }                        
 
                        if($bannersmiddle->isNotEmpty()) {
                            $data[] = ["title"=>"middlebanner", "type"=>"banner", "values"=>$bannersmiddle];
                        }   

                        if($subcategories->isNotEmpty()) {
                            $data[] = ["title"=>'subcategories', "type"=>"subcategories", "values"=>$subcategories];
                        }               

                        if($bannersbottom->isNotEmpty()) {
                            $data[] = ["title"=>"bottombanner", "type"=>"banner", "values"=>$bannersbottom];
                        }   

                        /*if($defaultuseraddress->isNotEmpty()) {
                            $data[] = ["title"=>"defaultuseraddress", "type"=>"useraddress", "values"=>$defaultuseraddress[0]];
                        }  */             

                        if(count($data)>0) {
                            return response()->json(['status' => 1, 'message' => 'Home Contents', 'data' => $data]);
                        }   else {
                            return response()->json(['status' => 0, 'message' => 'No Home Contents', 'data' => []]);
                        }
                    }   else {
                        return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid Inputs']);
                    }
                }
            }    else {
                return response()->json([ 'status' => 0, 'data' => [], 'message' => $error]);
            }
        }   catch(\Throwable $th) {
            return response()->json(['status' => 0, 'data' => [], 'message' => $th->getMessage()]);
        }  
    }


    /* Get Category based Sub Categories */
    public function getSubCategories(Request $request) {
        try {   
            $inputJSON = file_get_contents('php://input');

            $input = json_decode($inputJSON, TRUE);

            $requiredParams = ['user_id', 'api_token', 'category_id'];

            $error = $this->checkParams($input, $requiredParams, $request);

            if(empty($error)) {

                $userid = ((isset($input) && isset($input['user_id']))) ? $input['user_id'] : 0; 
                $category_id = ((isset($input) && isset($input['category_id']))) ? $input['category_id'] : 0; 

                $api_token = $request->header('x-api-key');
                $page_no = 0;  $limit = CommonController::$page_limit;

                $mes = User::checkTokenExpiry($userid, $api_token);
                $status = $mes['status'];   $message = $mes['message'];
                if($status != 1) {
                    return response()->json([ 'status' => $status, 'data' => [], 'message' => $message]);
                }   else {
                    if($userid > 0) { 
                        $subcategories = SubCategory::leftjoin('em_category', 'em_category.id', '=', 'em_sub_category.category_id')
                        ->where('em_sub_category.status', 'ACTIVE')
                        ->where('em_sub_category.category_id', $category_id)
                        ->select('em_sub_category.*', 'em_category.name as category_name')
                        ->orderby('position', 'asc')
                        ->get();

                        if(!empty($subcategories)){
                            return response()->json(['status' => 1, 'message' => 'Sub Category List', 'details'=>$subcategories]);
                        }   else {
                            return response()->json(['status' => 0, 'message' => 'No Sub Category List']);
                        }
                    }   else {
                        return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid Inputs']);
                    }
                }
            }    else {
                return response()->json([ 'status' => 0, 'data' => [], 'message' => $error]);
            }
        }   catch(\Throwable $th) {
            return response()->json(['status' => 0, 'data' => [], 'message' => $th->getMessage()]);
        }  
    }

    /* Get Sub Category based Services  */
    public function getServices(Request $request) {
        try {   
            $inputJSON = file_get_contents('php://input');

            $input = json_decode($inputJSON, TRUE);

            $requiredParams = ['user_id', 'api_token', 'sub_category_id'];

            $error = $this->checkParams($input, $requiredParams, $request);

            if(empty($error)) {

                $userid = ((isset($input) && isset($input['user_id']))) ? $input['user_id'] : 0; 
                $sub_category_id = ((isset($input) && isset($input['sub_category_id']))) ? $input['sub_category_id'] : 0; 

                $api_token = $request->header('x-api-key');
                $page_no = 0;  $limit = CommonController::$page_limit;

                $mes = User::checkTokenExpiry($userid, $api_token);
                $status = $mes['status'];   $message = $mes['message'];
                if($status != 1) {
                    return response()->json([ 'status' => $status, 'data' => [], 'message' => $message]);
                }   else {
                    if($userid > 0) { 
                        $services = Services::leftjoin('em_sub_category', 'em_sub_category.id', '=', 'em_sub_cat_services.sub_category_id')
                            ->leftjoin('em_category', 'em_category.id', '=', 'em_sub_category.category_id')
                            ->where('em_sub_cat_services.status', 'ACTIVE')
                            ->where('em_sub_cat_services.sub_category_id', $sub_category_id)
                            ->select('em_sub_cat_services.*', 'em_category.name as category_name', 'em_sub_category.name as sub_category_name')
                            ->orderby('position', 'asc')
                            ->get();

                        $subcategories = SubCategory::where('em_sub_category.status', 'ACTIVE')
                            ->where('em_sub_category.id', $sub_category_id)
                            ->select('em_sub_category.text1', 'em_sub_category.text2', 'em_sub_category.text3')
                            ->orderby('position', 'asc')
                            ->first();

                        if(!empty($services)){
                            return response()->json(['status' => 1, 'message' => 'Sub Category Service List', 'details'=>$services, 'text'=>$subcategories]);
                        }   else {
                            return response()->json(['status' => 0, 'message' => 'No Sub Category Service List']);
                        }
                    }   else {
                        return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid Inputs']);
                    }
                }
            }    else {
                return response()->json([ 'status' => 0, 'data' => [], 'message' => $error]);
            }
        }   catch(\Throwable $th) {
            return response()->json(['status' => 0, 'data' => [], 'message' => $th->getMessage()]);
        }  
    }

    /* Get Sub Category based Services  */
    public function getSubServices(Request $request) {
        try {   
            $inputJSON = file_get_contents('php://input');

            $input = json_decode($inputJSON, TRUE);

            $requiredParams = ['user_id', 'api_token', 'sub_category_id'];

            $error = $this->checkParams($input, $requiredParams, $request);

            if(empty($error)) {

                $service_id = (isset($input['service_id'])) ? $input['service_id'] : 0; 
                $sub_category_id = (isset($input['sub_category_id'])) ? $input['sub_category_id'] : ''; 
                $service_provider_id = (isset($input['service_provider_id'])) ? $input['service_provider_id'] : 0; 

                $userid = (isset($input['user_id'])) ? $input['user_id'] : 0;  

                $api_token = $request->header('x-api-key');
                $page_no = 0;  $limit = CommonController::$page_limit;

                $mes = User::checkTokenExpiry($userid, $api_token);
                $status = $mes['status'];   $message = $mes['message'];
                if($status != 1) {
                    return response()->json([ 'status' => $status, 'data' => [], 'message' => $message]);
                }   else {
                    if($userid > 0) {                        

                        SubCategory::$service_provider_id = $service_provider_id; 
                        Services::$service_provider_id = $service_provider_id;
                        SubServices::$service_provider_id = $service_provider_id;

                        /*if($service_provider_id >0) {
                            $subservices = SubCategory::with('services', 'services.subServices')
                                ->leftjoin('em_service_provider', \DB::raw("FIND_IN_SET(em_sub_category.id, em_service_provider.sub_category_ids)"),">",\DB::raw("'0'"))
                                ->where('em_service_provider.user_id', $service_provider_id)->select('em_sub_category.*')->get()->toArray();
                        }   else*/if(!empty($sub_category_id)) {
                            $sub_category_id = str_replace('[', '', $sub_category_id);
                            $sub_category_id = str_replace(']', '', $sub_category_id);
                            $sub_category_id = explode(',', $sub_category_id);

                            $subservices = SubCategory::with('services', 'services.subServices', 'services.subServices.servicersdetails')
                                ->whereIn('id', $sub_category_id)
                                ->orderby('position', 'asc')->get();
                        }   else {
                            Services::$user_id = $userid;
                            SubServices::$user_id = $userid;
                            $subservices = Services::with('SubServices')->where('id', $service_id)->orderby('position', 'asc')->get();
                        }        

                        if(!empty($subservices)){
                            return response()->json(['status' => 1, 'message' => 'Sub Service List', 'details'=>$subservices]);
                        }   else {
                            return response()->json(['status' => 0, 'message' => 'No Sub Service List']);
                        }
                    }   else {
                        return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid Inputs']);
                    }
                }
            }    else {
                return response()->json([ 'status' => 0, 'data' => [], 'message' => $error]);
            }
        }   catch(\Throwable $th) {
            return response()->json(['status' => 0, 'data' => [], 'message' => $th->getMessage()]);
        }  
    }

    /* Get Sub Services  */
    public function getSubCategoryServices(Request $request) {
        try {   
            $inputJSON = file_get_contents('php://input');

            $input = json_decode($inputJSON, TRUE);

            $requiredParams = ['user_id', 'api_token', 'sub_category_id'];

            $error = $this->checkParams($input, $requiredParams, $request);

            $service_id = (isset($input['service_id'])) ? $input['service_id'] : 0; 
            $sub_category_id = (isset($input['sub_category_id'])) ? $input['sub_category_id'] : ''; 

            if(empty($error)) {

                $sub_category_id = (isset($input['sub_category_id'])) ? $input['sub_category_id'] : '';  

                $userid = (isset($input['user_id'])) ? $input['user_id'] : 0;  

                $api_token = $request->header('x-api-key');
                $page_no = 0;  $limit = CommonController::$page_limit;

                $mes = User::checkTokenExpiry($userid, $api_token);
                $status = $mes['status'];   $message = $mes['message'];
                if($status != 1) {
                    return response()->json([ 'status' => $status, 'data' => [], 'message' => $message]);
                }   else {
                    if($userid > 0) { 

                        $services = Services::leftjoin('em_sub_category', 'em_sub_category.id', '=', 'em_sub_cat_services.sub_category_id')
                            ->leftjoin('em_category', 'em_category.id', '=', 'em_sub_category.category_id')
                            ->where('em_sub_cat_services.status', 'ACTIVE')
                            ->where('em_sub_cat_services.sub_category_id', $sub_category_id)
                            ->select('em_sub_cat_services.*', 'em_category.name as category_name', 'em_sub_category.name as sub_category_name')
                            ->orderby('position', 'asc')
                            ->get();

                        $subcategories = SubCategory::where('em_sub_category.status', 'ACTIVE')
                            ->where('em_sub_category.id', $sub_category_id)
                            ->select('em_sub_category.text1', 'em_sub_category.text2', 'em_sub_category.text3')
                            ->orderby('position', 'asc')
                            ->first();

                        if(!empty($services)){
                            return response()->json(['status' => 1, 'message' => 'Sub Category Service List', 'details'=>$services, 'text'=>$subcategories]);
                        }   else {
                            return response()->json(['status' => 0, 'message' => 'No Sub Category Service List']);
                        }
                    }   else {
                        return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid Inputs']);
                    }
                }
            }    else {
                return response()->json([ 'status' => 0, 'data' => [], 'message' => $error]);
            }
        }   catch(\Throwable $th) {
            return response()->json(['status' => 0, 'data' => [], 'message' => $th->getMessage()]);
        }  
    }

    /* Get Home Categories */
    public function getHomeCategories(Request $request) {
        try {   
            $inputJSON = file_get_contents('php://input');

            $input = json_decode($inputJSON, TRUE);

            $requiredParams = ['user_id', 'api_token'];

            $error = $this->checkParams($input, $requiredParams, $request);

            if(empty($error)) {

                $userid = ((isset($input) && isset($input['user_id']))) ? $input['user_id'] : 0; 
                $category_id = ((isset($input) && isset($input['category_id']))) ? $input['category_id'] : 0; 
                $page_no = ((isset($input) && isset($input['page_no']))) ? $input['page_no'] : 0; 

                $api_token = $request->header('x-api-key');
                $limit = CommonController::$page_limit;

                $mes = User::checkTokenExpiry($userid, $api_token);
                $status = $mes['status'];   $message = $mes['message'];
                if($status != 1) {
                    return response()->json([ 'status' => $status, 'data' => [], 'message' => $message]);
                }   else {
                    if($userid > 0) { 
                        $categories_qry  = Category::where('status', 'ACTIVE');
                        if($category_id > 0) {
                            $categories_qry->where('id', $category_id);
                        } 
                        
                        $categories = $categories_qry->select('id', 'id as homeCategoryId', 'name', 'name as homeName', 'type as homeType', 'image')->orderby('position', 'asc')->skip($page_no)->take($limit)->get(); 

                        if(!empty($categories)){
                            return response()->json(['status' => 1, 'message' => 'Category List', 'details'=>$categories]);
                        }   else {
                            return response()->json(['status' => 0, 'message' => 'No Category List']);
                        }
                    }   else {
                        return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid Inputs']);
                    }
                }
            }    else {
                return response()->json([ 'status' => 0, 'data' => [], 'message' => $error]);
            }
        }   catch(\Throwable $th) {
            return response()->json(['status' => 0, 'data' => [], 'message' => $th->getMessage()]);
        }  
    }    

    /* Get Home Sub Categories */
    public function getHomeSubCategories(Request $request) {
        try {   
            $inputJSON = file_get_contents('php://input');

            $input = json_decode($inputJSON, TRUE);

            $requiredParams = ['user_id', 'api_token'];

            $error = $this->checkParams($input, $requiredParams, $request);

            if(empty($error)) {

                $userid = ((isset($input) && isset($input['user_id']))) ? $input['user_id'] : 0; 
                $page_no = ((isset($input) && isset($input['page_no']))) ? $input['page_no'] : 0; 

                $api_token = $request->header('x-api-key');
                $limit = CommonController::$page_limit;

                $mes = User::checkTokenExpiry($userid, $api_token);
                $status = $mes['status'];   $message = $mes['message'];
                if($status != 1) {
                    return response()->json([ 'status' => $status, 'data' => [], 'message' => $message]);
                }   else {
                    if($userid > 0) { 
                        $sub_categories_qry  = SubCategory::where('status', 'ACTIVE')->where('home_display', 'YES');
                        $sub_categories = $sub_categories_qry->select('id', 'id as mainCategoryId', 'name', 'name as title', 'image')->orderby('position', 'asc')->skip($page_no)->take($limit)->get(); 

                        if(!empty($sub_categories)){
                            return response()->json(['status' => 1, 'message' => 'Sub Category List', 'details'=>$sub_categories]);
                        }   else {
                            return response()->json(['status' => 0, 'message' => 'No Sub Category List']);
                        }
                    }   else {
                        return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid Inputs']);
                    }
                }
            }    else {
                return response()->json([ 'status' => 0, 'data' => [], 'message' => $error]);
            }
        }   catch(\Throwable $th) {
            return response()->json(['status' => 0, 'data' => [], 'message' => $th->getMessage()]);
        }  
    }

    
    /* Get Sub Category Services */
    public function getSubCatServices(Request $request) {
        try {   
            $inputJSON = file_get_contents('php://input');

            $input = json_decode($inputJSON, TRUE);

            $requiredParams = ['user_id', 'api_token', 'sub_category_id'];

            $error = $this->checkParams($input, $requiredParams, $request);

            if(empty($error)) {

                $userid = ((isset($input) && isset($input['user_id']))) ? $input['user_id'] : 0; 
                $sub_category_id = ((isset($input) && isset($input['sub_category_id']))) ? $input['sub_category_id'] : 0; 
                $page_no = ((isset($input) && isset($input['page_no']))) ? $input['page_no'] : 0; 

                $api_token = $request->header('x-api-key');
                $limit = CommonController::$page_limit;

                $mes = User::checkTokenExpiry($userid, $api_token);
                $status = $mes['status'];   $message = $mes['message'];
                if($status != 1) {
                    return response()->json([ 'status' => $status, 'data' => [], 'message' => $message]);
                }   else {
                    if($userid > 0) { 
                        $sub_category  = SubCategory::leftjoin('em_category', 'em_category.id', '=', 'em_sub_category.category_id')
                            ->with('subCategoryItems')->where('em_sub_category.status', 'ACTIVE')
                            ->where('em_sub_category.id', $sub_category_id)
                            ->select('em_sub_category.id', 'em_sub_category.id as mainCategoryId', 'em_sub_category.name as mainCategoryName',  'em_sub_category.video_link',
                                'em_sub_category.text1 as mainCategoryLabel1', 'em_sub_category.text2 as mainCategoryLabel2', 
                                'em_sub_category.text3 as mainCategoryLabel3', 'em_sub_category.description', 'em_sub_category.image','em_category.name as category_name' 
                            )
                            ->first(); 

                        $rating = DB::table('em_booking')
                            ->leftjoin('em_booking_subservices', 'em_booking.id', 'em_booking_subservices.booking_id')
                            ->where('rating', '>=', 0)
                            ->where('em_booking_subservices.sub_category_id', $sub_category_id)
                            ->leftjoin('users', 'users.id', 'em_booking.user_id')->where('rating', '>', 0)
                            ->select('em_booking.id as booking_id', 'rating', 'rating_comment', 'rated_date', 'em_booking.user_id', 'users.name')
                            ->groupBy('em_booking_subservices.booking_id')->orderby('rating', 'DESC')->skip(0)->take(10)->get();
                        if(!empty($rating)) {
                        }   else {
                            $rating = [];
                        }

                        if(!empty($sub_category)){
                            return response()->json(['status' => 1, 'message' => 'Sub Category List', 'details'=>$sub_category, 'rating'=>$rating]);
                        }   else {
                            return response()->json(['status' => 0, 'message' => 'No Sub Category List', 'rating'=>$rating]);
                        }
                    }   else {
                        return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid Inputs']);
                    }
                }
            }    else {
                return response()->json([ 'status' => 0, 'data' => [], 'message' => $error]);
            }
        }   catch(\Throwable $th) {
            return response()->json(['status' => 0, 'data' => [], 'message' => $th->getMessage()]);
        }  
    }

    /* Get Sub Services */
    public function getSubServiceProducts(Request $request) {
        try {   
            $inputJSON = file_get_contents('php://input');

            $input = json_decode($inputJSON, TRUE);

            $requiredParams = ['user_id', 'api_token', 'service_id']; //sub_category_id

            $error = $this->checkParams($input, $requiredParams, $request);

            if(empty($error)) {

                $userid = ((isset($input) && isset($input['user_id']))) ? $input['user_id'] : 0; 
        //$sub_category_id = ((isset($input) && isset($input['sub_category_id']))) ? $input['sub_category_id'] :      0; 
                $service_id = ((isset($input) && isset($input['service_id']))) ? $input['service_id'] : 0; 
                $page_no = ((isset($input) && isset($input['page_no']))) ? $input['page_no'] : 0; 

                $api_token = $request->header('x-api-key');
                $limit = CommonController::$page_limit;

                $mes = User::checkTokenExpiry($userid, $api_token);
                $status = $mes['status'];   $message = $mes['message'];
                if($status != 1) {
                    return response()->json([ 'status' => $status, 'data' => [], 'message' => $message]);
                }   else {
                    if($userid > 0) {

                        $sub_category_id = DB::table('em_sub_cat_services')->where('id', $service_id)->value('sub_category_id');

                        $additional_charge = DB::table('em_admin_settings')->where('id', 1)
                            ->whereRaw("FIND_IN_SET(".$sub_category_id.", em_admin_settings.additional_category_ids)") 
                            ->select('enable_additional_charges', 'additional_charges', 'additional_charge_text')->first();

                        if(empty($additional_charge)) {
                            $additional_charge = [
                                "enable_additional_charges" => 0,
                                "additional_charges" => 0,
                                "additional_charge_text" => ""];
                        }

                        Services::$user_id = $userid;
                        SubServices::$user_id = $userid;
                        $subservices = Services::leftjoin('em_sub_category', 'em_sub_category.id', 'em_sub_cat_services.sub_category_id')

                            ->with(['products' => function($query) {
                             // user_id is required here*
                             $query->select(['id', 'id as productId', 'name as productTitle', 
                                'description as productDescription', 'price as productPrice', 'offer_price', 'service_id', 'created_at as productDate']);
                         }])
                        //->where('sub_category_id', $sub_category_id)
                            ->where('em_sub_cat_services.id', $service_id)->where('em_sub_cat_services.status', 'ACTIVE')
                            ->select('em_sub_cat_services.*', 'em_sub_cat_services.name as title', 'em_sub_category.name as sub_category_name')->orderby('position', 'asc')->get();

                        if(!empty($subservices)){
                            return response()->json(['status' => 1, 'message' => 'Sub Services List', 'details'=>$subservices, 
                                'additional_charge'=>$additional_charge]);
                        }   else {
                            return response()->json(['status' => 0, 'message' => 'No Sub Services List', 'additional_charge'=>$additional_charge]);
                        }
                    }   else {
                        return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid Inputs']);
                    }
                }
            }    else {
                return response()->json([ 'status' => 0, 'data' => [], 'message' => $error]);
            }
        }   catch(\Throwable $th) {
            return response()->json(['status' => 0, 'data' => [], 'message' => $th->getMessage()]);
        }  
    }

    /* User Add Sub Services to the Cart */
    public function getAddToCart(Request $request) {
        try {   
            $inputJSON = file_get_contents('php://input');

            $input = json_decode($inputJSON, TRUE);

            $requiredParams = ['user_id', 'api_token', 'product_id', "type"];

            $error = $this->checkParams($input, $requiredParams, $request);

            if(empty($error)) {

                $userid = ((isset($input) && isset($input['user_id']))) ? $input['user_id'] : 0; 
                $product_id = ((isset($input) && isset($input['product_id']))) ? $input['product_id'] : 0;  
                $type = ((isset($input) && isset($input['type']))) ? $input['type'] : 1; 

                $api_token = $request->header('x-api-key');
                $limit = CommonController::$page_limit;

                $mes = User::checkTokenExpiry($userid, $api_token);
                $status = $mes['status'];   $message = $mes['message'];
                if($status != 1) {
                    return response()->json([ 'status' => $status, 'data' => [], 'message' => $message]);
                }   else {
                    if($userid > 0) { 

                        $user_address = DB::table('users_address')
                                            ->where('user_id', $userid)->where('is_default', 1)->first();

                        if(!empty($user_address)) {
                            $user_address_id = $user_address->id;
                        }   else {
                            return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Please set the User Address']);
                        }

                        if ($type == '1') {
                            if($product_id > 0) {
                                $service_id = DB::table('em_sub_service')->where('id', $product_id)->value('service_id');
                                $sub_category_id = DB::table('em_sub_cat_services')->where('id', $service_id)->value('sub_category_id');

                                $additional_charge = DB::table('em_admin_settings')->where('id', 1)
                                    ->whereRaw("FIND_IN_SET(".$sub_category_id.", em_admin_settings.additional_category_ids)") 
                                    ->select('enable_additional_charges', 'additional_charges', 'additional_charge_text')
                                    ->first();

                                $enable_additional_charges = 0;
                                $additional_charges = 0;
                                $additional_charge_text = '';
                                if(!empty($additional_charge)) {
                                    $enable_additional_charges = $additional_charge->enable_additional_charges;
                                    $additional_charges = $additional_charge->additional_charges;
                                    $additional_charge_text = $additional_charge->additional_charge_text;
                                }
                            
                                $subservice = SubServices::find($product_id);
                                if(!empty($subservice)) {
                                    $product_price = $subservice->price;
                                    $product_title = $subservice->name;
                                    $product_description = $subservice->description;

                                    $subservice_details = DB::table('em_sub_service')
                                        ->select('em_sub_category.name as mainCategoryName', 'em_sub_service.price')
                                        ->leftjoin('em_sub_cat_services', 'em_sub_cat_services.id', 'em_sub_service.service_id')
                                        ->leftjoin('em_sub_category', 'em_sub_category.id', 'em_sub_cat_services.sub_category_id')
                                        ->where('em_sub_service.id', $product_id)
                                        ->first();

                                    $mainCategoryName = ''; $price = 0;
                                    if(!empty($subservice_details)) {
                                        $mainCategoryName = $subservice_details->mainCategoryName;
                                        $price = $subservice_details->price;
                                    }

                                    $cart = Cart::where('user_id', $userid)->get();
                                    if($cart->isNotEmpty()) {
                                        $cart = $cart[0];
                                        $updated_at = date('Y-m-d');
                                        $cart_id = (int) $cart->id;
                                        $estimate = (float) $cart->cart_total;
                                        $user_address_id = (int) $cart->user_address_id;
                                        $date = $cart->job_date;
                                        $slot_id = (int) $cart->job_slot;

                                        $cart_item = CartItem::where('sub_service_id', $product_id)
                                            ->where('cart_id', $cart_id)
                                            ->first();

                                        if(!empty($cart_item)) {

                                            $productprice = (float) $cart_item->price;
                                            $total_price = (float) $cart_item->total_price;
                                            $productcount = (int) $cart_item->qty;

                                            $qty = $productcount + 1;

                                            $pricePerItem = $productprice;
                                            $updatedPrice = $qty * $pricePerItem;

                                            CartItem::where('sub_service_id', $product_id)
                                                ->where('cart_id', $cart_id)
                                                ->update([
                                                    'qty' => $qty,
                                                    'price' => $price,
                                                    'total_price' => $updatedPrice,
                                                    'updated_at' => date('Y-m-d H:i:s')
                                                ]); 

                                            $sum_price = CartItem::where('cart_id', $cart_id)->sum('total_price');

                                            $total = $sum_price + $additional_charges;
                                            
                                            Cart::where('id', $cart_id)->where('user_id', $userid)
                                                ->update([
                                                    'sub_total' => $sum_price,
                                                    'additional_charge' => $additional_charges,
                                                    'additional_charge_text' => $additional_charge_text,
                                                    'cart_total' => $total,
                                                    'updated_at' => date('Y-m-d H:i:s')
                                                ]);  
 
                                            $cart = Cart::with('cartItems')->where('id', $cart_id)
                                                ->select('em_cart.id', 'em_cart.id as cartId', 'type', 'user_id',
                                                 'em_cart.cart_total as estimate', 'user_address_id', 
                                                 'job_date as date', 'job_slot as slot_id')
                                                ->get();
                                            if($cart->isNotEmpty()) {
                                                return response()->json([ 'status' => 1, 'data' => $cart, 'message' => 'Cart Items']);
                                            }   else {
                                                return response()->json([ 'status' => 0, 'data' => [], 'message' => 'No Items in Cart']);
                                            }

                                        }   else {
                                            $ex_sub_category_id = '';
                                            $ex_sub_category = CartItem::where('cart_id', $cart_id)->first();
                                            if(!empty($ex_sub_category)) {
                                                $ex_sub_category_id = $ex_sub_category->sub_category_id;
                                            }  

                                            $new_sub_category = SubServices::leftjoin('em_sub_cat_services', 'em_sub_service.service_id', 'em_sub_cat_services.id')
                                                ->select('em_sub_cat_services.sub_category_id', 'em_sub_cat_services.id',
                                                         'em_sub_service.price', 'em_sub_service.offer_price')
                                                ->where('em_sub_service.id', $product_id)->first();

                                            if(!empty($new_sub_category)) {
                                                $new_sub_category_id = $new_sub_category->sub_category_id;
                                                $service_id = $new_sub_category->id;
                                                $price = $new_sub_category->offer_price;
                                            }

                                            if(!empty($ex_sub_category_id)) {

                                                if($ex_sub_category_id != $new_sub_category_id) {
                                                    return response()->json([ 'status' => 0, 'data' => [], 'message' => 'This product is from new category. Do you wish to clear cart and add this product?']);
                                                }
                                            }

                                            $qty = 1;

                                            $cartsubservices = new CartItem;

                                            $cartsubservices->sub_category_id = $new_sub_category_id;

                                            $cartsubservices->user_id = $userid;

                                            $cartsubservices->cart_id = $cart_id;

                                            $cartsubservices->service_id = $service_id;

                                            $cartsubservices->sub_service_id = $product_id;

                                            $cartsubservices->qty = $qty;

                                            $cartsubservices->price = $price;

                                            $cartsubservices->total_price = $qty * $price;

                                            $cartsubservices->save();

                                            $sum_price = CartItem::where('cart_id', $cart_id)->sum('total_price');

                                            $total = $sum_price + $additional_charges;
                                            
                                            Cart::where('id', $cart_id)->where('user_id', $userid)
                                                ->update([
                                                    'user_address_id' => $user_address_id,
                                                    'sub_total' => $sum_price,
                                                    'additional_charge' => $additional_charges,
                                                    'additional_charge_text' => $additional_charge_text,
                                                    'cart_total' => $total,
                                                    'updated_at' => date('Y-m-d H:i:s')
                                                ]);  

                                            $cart = Cart::with('cartItems')->where('id', $cart_id)
                                                ->select('em_cart.id', 'em_cart.id as cartId', 'type', 'user_id',
                                                 'em_cart.cart_total as estimate', 'user_address_id', 
                                                 'job_date as date', 'job_slot as slot_id')
                                                ->get();
                                            if($cart->isNotEmpty()) {
                                                return response()->json([ 'status' => 1, 'data' => $cart, 'message' => 'Cart Items']);
                                            }   else {
                                                return response()->json([ 'status' => 0, 'data' => [], 'message' => 'No Items in Cart']);
                                            }
                                            
                                        }

                                    }   else {
                                        $new_sub_category = SubServices::leftjoin('em_sub_cat_services', 'em_sub_service.service_id', 'em_sub_cat_services.id')
                                            ->select('em_sub_cat_services.sub_category_id', 'em_sub_cat_services.id',
                                                     'em_sub_service.price', 'em_sub_service.offer_price')
                                            ->where('em_sub_service.id', $product_id)->first();

                                        if(!empty($new_sub_category)) {
                                            $new_sub_category_id = $new_sub_category->sub_category_id;
                                            $service_id = $new_sub_category->id;
                                            $price = $new_sub_category->offer_price;
                                        } 

                                        $cart = new Cart;

                                        $cart->user_address_id = $user_address_id;

                                        $cart->type = $type;

                                        $cart->user_id = $userid;

                                        $cart->save();

                                        $cart_id = $cart->id;

                                        $qty = 1;

                                        $cartsubservices = new CartItem;

                                        $cartsubservices->sub_category_id = $new_sub_category_id;

                                        $cartsubservices->user_id = $userid;

                                        $cartsubservices->cart_id = $cart_id;

                                        $cartsubservices->service_id = $service_id;

                                        $cartsubservices->sub_service_id = $product_id;

                                        $cartsubservices->qty = $qty;

                                        $cartsubservices->price = $price;

                                        $cartsubservices->total_price = $qty * $price;

                                        $cartsubservices->save();

                                        $sum_price = CartItem::where('cart_id', $cart_id)->sum('total_price');

                                        $total = $sum_price + $additional_charges;

                                        Cart::where('id', $cart_id)->where('user_id', $userid)
                                            ->update([
                                                'sub_total' => $sum_price,
                                                'additional_charge' => $additional_charges,
                                                'additional_charge_text' => $additional_charge_text,
                                                'cart_total' => $total,
                                                'updated_at' => date('Y-m-d H:i:s')
                                            ]);  

                                        $cart = Cart::with('cartItems')->where('id', $cart_id)
                                            ->select('em_cart.id', 'em_cart.id as cartId', 'type', 'user_id',
                                             'em_cart.cart_total as estimate', 'user_address_id', 
                                             'job_date as date', 'job_slot as slot_id')
                                            ->get();
                                        if($cart->isNotEmpty()) {
                                            return response()->json([ 'status' => 1, 'data' => $cart, 'message' => 'Cart Items']);
                                        }   else {
                                            return response()->json([ 'status' => 0, 'data' => [], 'message' => 'No Items in Cart']);
                                        }
                                    } 
                                }  else {
                                    return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid Product']);
                                }
                            }   else {
                                return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid Product Id']);
                            }  
                        } else {
                            return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid Type']);
                        }
                    }   else {
                        return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid Inputs']);
                    }
                }
            }    else {
                return response()->json([ 'status' => 0, 'data' => [], 'message' => $error]);
            }
        }   catch(\Throwable $th) {
            return response()->json(['status' => 0, 'data' => [], 'message' => $th->getMessage()]);
        }  
    }

    
    /* Delete Items from the Cart */
    public function getDeleteToCart(Request $request) {
        try {   
            $inputJSON = file_get_contents('php://input');

            $input = json_decode($inputJSON, TRUE);

            $requiredParams = ['user_id', 'api_token', 'type', 'product_id'];

            $error = $this->checkParams($input, $requiredParams, $request);

            if(empty($error)) {

                $userid = ((isset($input) && isset($input['user_id']))) ? $input['user_id'] : 0; 
                $type = ((isset($input) && isset($input['type']))) ? $input['type'] : 1; 
                $product_id = ((isset($input) && isset($input['product_id']))) ? $input['product_id'] : 0; 

                $api_token = $request->header('x-api-key');
                $limit = CommonController::$page_limit;

                $mes = User::checkTokenExpiry($userid, $api_token);
                $status = $mes['status'];   $message = $mes['message'];
                if($status != 1) {
                    return response()->json([ 'status' => $status, 'data' => [], 'message' => $message]);
                }   else {
                    if($userid > 0) { 
                        if($product_id > 0) {
                            $service_id = DB::table('em_sub_service')->where('id', $product_id)->value('service_id');
                            $sub_category_id = DB::table('em_sub_cat_services')->where('id', $service_id)->value('sub_category_id');

                            $additional_charge = DB::table('em_admin_settings')->where('id', 1)
                                ->whereRaw("FIND_IN_SET(".$sub_category_id.", em_admin_settings.additional_category_ids)") 
                                ->select('enable_additional_charges', 'additional_charges', 'additional_charge_text') 
                                ->first();
                        
                            $cart = Cart::where('user_id', $userid)->get();
                            if($cart->isNotEmpty()) {
                                $cart_id = (int) $cart[0]->id;

                                $cartitemscount =  CartItem::where('cart_id', $cart_id)->count();

                                if($cartitemscount == 1) {
                                    //only one product is in cart
                                    $cartproitemscount =  CartItem::where('cart_id', $cart_id)
                                        ->where('sub_service_id', $product_id)->count();

                                    if($cartproitemscount == 0) {
                                        return response()->json([ 'status' => 0, 'data' => [], 'message' => "This product is not in the cart for current user."]);
                                    }   else if($cartproitemscount == 1) {
                                        $cartproitems =  CartItem::where('cart_id', $cart_id)
                                            ->where('sub_service_id', $product_id)->get();

                                        if($cartproitems->isNotEmpty()) {
                                            $qty = $cartproitems[0]->qty;
                                            if ($qty == 1) {

                                                CartItem::where('cart_id', $cart_id)
                                                    ->where('sub_service_id', $product_id)->delete();

                                                $cartitemcount = CartItem::where('cart_id', $cart_id)->count();

                                                if($cartitemcount == 0) {
                                                    Cart::where('user_id', $userid)->delete();
                                                }

                                                return response()->json([ 'status' => 2, 'data' => [], 'message' => "Item deleted successfully, cart is empty now."]);
                                            }   else if($qty > 1) {
                                                $productprice = (float) $cartproitems[0]->total_price;
                                                $productcount = (int) $cartproitems[0]->qty;

                                                $qty = $productcount-1;

                                                $pricePerItem = (float) $cartproitems[0]->price;
                                                $updatedPrice = $qty * $pricePerItem;

                                                CartItem::where('cart_id', $cart_id)
                                                    ->where('sub_service_id', $product_id)
                                                    ->update([
                                                        'qty' => $qty,
                                                        'price' => $pricePerItem,
                                                        'total_price' => $updatedPrice,
                                                        'updated_at' => date('Y-m-d H:i:s')
                                                    ]);

                                                $sum_price = CartItem::where('cart_id', $cart_id)->sum('total_price');

                                                $enable_additional_charges = 0;
                                                $additional_charges = 0;
                                                $additional_charge_text = '';
                                                if(!empty($additional_charge)) {
                                                    $enable_additional_charges = $additional_charge->enable_additional_charges;
                                                    $additional_charges = $additional_charge->additional_charges;
                                                    $additional_charge_text = $additional_charge->additional_charge_text;
                                                }

                                                $total = $sum_price + $additional_charges;
                                                
                                                Cart::where('id', $cart_id)->where('user_id', $userid)
                                                    ->update([
                                                        'sub_total' => $sum_price,
                                                        'additional_charge' => $additional_charges,
                                                        'additional_charge_text' => $additional_charge_text,
                                                        'cart_total' => $total,
                                                        'updated_at' => date('Y-m-d H:i:s')
                                                    ]);   

                                                $cart = Cart::with('cartItems')->where('id', $cart_id)
                                                    ->select('em_cart.id', 'em_cart.id as cartId', 'type', 'user_id',
                                                     'em_cart.cart_total as estimate', 'user_address_id', 
                                                     'job_date as date', 'job_slot as slot_id')
                                                    ->get();
                                                if($cart->isNotEmpty()) {
                                                    return response()->json([ 'status' => 1, 'data' => $cart, 'message' => 'Cart Items']);
                                                }   else {
                                                    return response()->json([ 'status' => 0, 'data' => [], 'message' => 'No Items in Cart']);
                                                } 
                                            }
                                        }
                                    }
                                }   else if($cartitemscount > 1) {
                                    //not only this item, some other items also there in cart for this user

                                    $checkcartitem = CartItem::where('cart_id', $cart_id)
                                            ->where('sub_service_id', $product_id)
                                            ->count();

                                    if($checkcartitem == 0) {
                                        return response()->json([ 'status' => 0, 'data' => [], 'message' => 'This product is not in the cart for current user.']);
                                    }   else if($checkcartitem == 1) {
                                        $cartproitems =  CartItem::where('cart_id', $cart_id)
                                            ->where('sub_service_id', $product_id)->get();

                                        if($cartproitems->isNotEmpty()) {
                                            $qty = $cartproitems[0]->qty;
                                            $total_price = $cartproitems[0]->total_price;

                                            if($qty == 1) {
                                                CartItem::where('cart_id', $cart_id)
                                                    ->where('sub_service_id', $product_id)->delete();
                                            }   else if($qty > 1) {
                                                $productprice = (float) $cartproitems[0]->total_price;
                                                $productcount = (int) $cartproitems[0]->qty;

                                                $qty = $productcount-1;

                                                $pricePerItem = (float) $cartproitems[0]->price;
                                                $updatedPrice = $qty * $pricePerItem;

                                                CartItem::where('cart_id', $cart_id)
                                                    ->where('sub_service_id', $product_id)
                                                    ->update([
                                                        'qty' => $qty,
                                                        'price' => $pricePerItem,
                                                        'total_price' => $updatedPrice,
                                                        'updated_at' => date('Y-m-d H:i:s')
                                                    ]);
                                            }

                                            $sum_price = CartItem::where('cart_id', $cart_id)->sum('total_price');

                                            $enable_additional_charges = 0;
                                            $additional_charges = 0;
                                            $additional_charge_text = '';
                                            if(!empty($additional_charge)) {
                                                $enable_additional_charges = $additional_charge->enable_additional_charges;
                                                $additional_charges = $additional_charge->additional_charges;
                                                $additional_charge_text = $additional_charge->additional_charge_text;
                                            }

                                            $total = $sum_price + $additional_charges;
                                            
                                            Cart::where('id', $cart_id)->where('user_id', $userid)
                                                ->update([
                                                    'sub_total' => $sum_price,
                                                    'additional_charge' => $additional_charges,
                                                    'additional_charge_text' => $additional_charge_text,
                                                    'cart_total' => $total,
                                                    'updated_at' => date('Y-m-d H:i:s')
                                                ]); 

                                            $cart = Cart::with('cartItems')->where('id', $cart_id)
                                                ->select('em_cart.id', 'em_cart.id as cartId', 'type', 'user_id',
                                                 'em_cart.cart_total as estimate', 'user_address_id', 
                                                 'job_date as date', 'job_slot as slot_id')
                                                ->get();
                                            if($cart->isNotEmpty()) {
                                                return response()->json([ 'status' => 1, 'data' => $cart, 'message' => 'Cart Items']);
                                            }   else {
                                                return response()->json([ 'status' => 0, 'data' => [], 'message' => 'No Items in Cart']);
                                            } 
                                        }   else {
                                            return response()->json([ 'status' => 0, 'data' => [], 'message' => 'This product is not in the cart for current user.']);
                                        }
                                    }             
                                }
                            }   else {
                                return response()->json([ 'status' => 0, 'data' => [], 'message' => 'There is no cart items for this user.']);
                            } 
                        }   else {
                            return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid Product Id']);
                        } 
                    }   else {
                        return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid Inputs']);
                    }
                }
            }    else {
                return response()->json([ 'status' => 0, 'data' => [], 'message' => $error]);
            }
        }   catch(\Throwable $th) {
            return response()->json(['status' => 0, 'data' => [], 'message' => $th->getMessage()]);
        }  
    }

    /* post Confirm Slot for the Cart */
    public function postConfirmSlotCart_old(Request $request) {
        try {   
            $inputJSON = file_get_contents('php://input');

            $input = json_decode($inputJSON, TRUE);

            $requiredParams = ['user_id', 'api_token', 'user_address_id', 'slot_id', 'job_date'];

            $error = $this->checkParams($input, $requiredParams, $request);

            if(empty($error)) {

                $userid = ((isset($input) && isset($input['user_id']))) ? $input['user_id'] : 0;  
                $user_address_id = ((isset($input) && isset($input['user_address_id']))) ? $input['user_address_id'] : 0;  
                $job_date = ((isset($input) && isset($input['job_date']))) ? $input['job_date'] : date('Y-m-d');  
                $slot_id = ((isset($input) && isset($input['slot_id']))) ? $input['slot_id'] : 0;  

                $api_token = $request->header('x-api-key');
                $limit = CommonController::$page_limit;

                $mes = User::checkTokenExpiry($userid, $api_token);
                $status = $mes['status'];   $message = $mes['message'];
                if($status != 1) {
                    return response()->json([ 'status' => $status, 'data' => [], 'message' => $message]);
                }   else {
                    if($userid > 0) { 
                        $date = date('Y-m-d');
                        if (strtotime($job_date) < strtotime($date)) {
                            return response()->json([ 'status' => 0, 'data' => [], 'message' => "Past date is supplied"]);
                        }

                        $cart = Cart::where('user_id', $userid)->get();
                        if($cart->isNotEmpty()) {
                            $getzone = UserAddress::where('user_id', $userid)
                                ->where('id', $user_address_id)
                                ->where('status', 'ACTIVE')
                                ->select('zone_id')->get();

                            if($getzone->isNotEmpty()) {
                                $chk = $this->checkZoneId($getzone[0]->zone_id);
                                if($chk) {
                                    $zone_id = $getzone[0]->zone_id;
                                    $res = DB::select("SELECT slot_id, (SELECT count(emp_id) as count from em_available_slot a WHERE a.slot_id = b.slot_id AND  date='$job_date' AND available='YES' AND zone_id=$zone_id) as count, s.slot_name as name from em_available_slot b INNER JOIN em_slots s ON s.id = b.slot_id
                                    WHERE b.slot_id = $slot_id limit 1"); 
                                    if(!empty($res)) {
                                        $current_row = $res[0];

                                        //check already booked
                                        $bookedalreadycheck_qry = "SELECT b.job_slot,COUNT(b.service_provider_id) as count FROM em_booking b 
                                            INNER JOIN em_slots s ON b.job_slot = s.id
                                            WHERE b.job_slot=$slot_id
                                            AND b.job_date='$job_date'
                                            AND b.status IN ('PLACED','ACCEPTED','RESCHEDULED')
                                            AND b.payment_status = 'PENDING' group by b.job_slot";

                                        $bookedalreadycheck = DB::select($bookedalreadycheck_qry);

                                        $bookedCount = 0;
                                        if(!empty($bookedalreadycheck)) {
                                            $bookedCount =  $bookedalreadycheck[0]->count;
                                        }

                                        $resultantCount = (int) $current_row->count - $bookedCount;
                                        //already booked block ends here

                                        if ($resultantCount > 0) {

                                            Cart::where('user_id', $userid)
                                                ->update([
                                                    'job_date' => $job_date,
                                                    'job_slot' => $slot_id,
                                                    'user_address_id' => $user_address_id,
                                                ]);

                                            $cart = Cart::with('cartItems')->where('user_id', $userid)
                                                ->select('em_cart.id', 'em_cart.id as cartId', 'type', 'user_id',
                                                 'em_cart.cart_total as estimate', 'user_address_id', 
                                                 'job_date as date', 'job_slot as slot_id')
                                                ->get();
                                            if($cart->isNotEmpty()) {
                                                return response()->json([ 'status' => 1, 'data' => $cart, 'message' => 'Cart Items']);
                                            }   else {
                                                return response()->json([ 'status' => 0, 'data' => [], 'message' => 'No Items in Cart']);
                                            } 
                                        } else {
                                            return response()->json([ 'status' => 0, 'data' => [], 'message' => "This slot is not available for user's address zone and given date"]);
                                        }
                                    }   else {
                                        return response()->json([ 'status' => 0, 'data' => [], 'message' => "This slot is not available"]);
                                    }
                                }   else {
                                    return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Not a valid Zone']);
                                }
                            }   else {
                                return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Not a valid User Address Id']);
                            }                             
                        }   else {
                            return response()->json([ 'status' => 0, 'data' => [], 'message' => 'No Items in Cart']);
                        }

                        $cart = Cart::with('cartItems')->where('user_id', $userid)
                            ->select('em_cart.id', 'em_cart.id as cartId', 'type', 'user_id',
                             'em_cart.cart_total as estimate', 'user_address_id', 
                             'job_date as date', 'job_slot as slot_id')
                            ->get();
                        if($cart->isNotEmpty()) {
                            return response()->json([ 'status' => 1, 'data' => $cart, 'message' => 'Cart Items']);
                        }   else {
                            return response()->json([ 'status' => 0, 'data' => [], 'message' => 'No Items in Cart']);
                        } 
                    }   else {
                        return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid Inputs']);
                    }
                }
            }    else {
                return response()->json([ 'status' => 0, 'data' => [], 'message' => $error]);
            }
        }   catch(\Throwable $th) {
            return response()->json(['status' => 0, 'data' => [], 'message' => $th->getMessage()]);
        }  
    }

    public function getZoneServicers($zone_id) {
        $servicers_arr = [];
        if($zone_id > 0) {
            $servicers = DB::table('users')
                ->leftjoin('em_service_provider', 'em_service_provider.user_id', 'users.id')
                ->whereRaw("FIND_IN_SET(".$zone_id.", em_service_provider.zone_ids)")
                ->where('users.status', 'ACTIVE')
                ->where('users.approve_status', 'APPROVED')
                ->select('users.id')->get();

            
            if($servicers->isNotEmpty()) {
                foreach ($servicers as $key => $value) {
                    $servicers_arr[] = $value->id;
                }
            }
        }   
        return $servicers_arr;
    }

    /* post Confirm Slot for the Cart */
    public function postConfirmSlotCart(Request $request) {
        try {   
            $inputJSON = file_get_contents('php://input');

            $input = json_decode($inputJSON, TRUE);

            $requiredParams = ['user_id', 'api_token', 'user_address_id', 'slot_id', 'job_date'];

            $error = $this->checkParams($input, $requiredParams, $request);

            if(empty($error)) {

                $userid = ((isset($input) && isset($input['user_id']))) ? $input['user_id'] : 0;  
                $user_address_id = ((isset($input) && isset($input['user_address_id']))) ? $input['user_address_id'] : 0;  
                $job_date = ((isset($input) && isset($input['job_date']))) ? date('Y-m-d', strtotime($input['job_date'])) : date('Y-m-d');  
                $slot_id = ((isset($input) && isset($input['slot_id']))) ? $input['slot_id'] : 0;  

                $api_token = $request->header('x-api-key');
                $limit = CommonController::$page_limit;

                $mes = User::checkTokenExpiry($userid, $api_token);
                $status = $mes['status'];   $message = $mes['message'];
                if($status != 1) {
                    return response()->json([ 'status' => $status, 'data' => [], 'message' => $message]);
                }   else {
                    if($userid > 0) { 
                        $date = date('Y-m-d');
                        if (strtotime($job_date) < strtotime($date)) {
                            return response()->json([ 'status' => 0, 'data' => [], 'message' => "Past date is supplied"]);
                        }

                        $cart = Cart::where('user_id', $userid)->get();
                        if($cart->isNotEmpty()) {
                            $getzone = UserAddress::where('user_id', $userid)
                                ->where('id', $user_address_id)
                                ->where('status', 'ACTIVE')
                                ->select('zone_id')->get();

                            if($getzone->isNotEmpty()) {
                                $chk = $this->checkZoneId($getzone[0]->zone_id);
                                if($chk) {
                                    $zone_id = $getzone[0]->zone_id;

                                    $slots = Slots::where('status', 'ACTIVE')->where('id', $slot_id)
                                        ->first(); 
                                    $finalslots = [];
                                    if(!empty($slots)) {
                                        $current_time = date('H:i');

                                        $delivery_date = $job_date;

                                        $timestamp = strtotime($delivery_date.' '.$current_time);
                                        $newslots = []; 

                                        $timestamp_from = strtotime($slots->from_time);
                                        $timestamp_to = strtotime($slots->to_time);
 
                                        if($delivery_date == date("Y-m-d")) {  
                                            if($timestamp_from > $timestamp) {
                                                $newslots[strtotime($delivery_date)][$timestamp_from] = $slots;
                                            }
                                        }   else if(strtotime($delivery_date) > strtotime(date("Y-m-d"))) { 
                                            $newslots[strtotime($delivery_date)][$timestamp_from] = $slots;
                                        }
                                       
                                        ksort($newslots);
                                        foreach($newslots as $sk=>$slot) {
                                            foreach($slot as $fslot) {
                                                $finalslots[] = $fslot;
                                            }
                                        }
                                    }

                                    $servicers_arr = $this->getZoneServicers($zone_id);

                                    foreach($finalslots as $sk=>$slot) {
                                        
                                        $leave_servicers = DB::table('em_servicer_leave_slots')
                                            ->whereRaw("FIND_IN_SET(".$slot_id.", em_servicer_leave_slots.Leave_slots)")
                                            ->where('leave_date', $job_date)
                                            ->select('servicer_id')->get(); 

                                        $leave_servicers_arr = [];
                                        if($leave_servicers->isNotEmpty()) {
                                            foreach ($leave_servicers as $key => $value) {
                                                $leave_servicers_arr[] = $value->servicer_id;
                                            }

                                            $result=array_diff($servicers_arr,$leave_servicers_arr);
                                            if(count($result) == 0) {
                                                unset($finalslots[$sk]);
                                            }
                                        }
                                    }

                                    if(!empty($finalslots)){
                                        Cart::where('user_id', $userid)
                                        ->update([
                                            'job_date' => $job_date,
                                            'job_slot' => $slot_id,
                                            'user_address_id' => $user_address_id,
                                        ]);
                                    }   else {
                                        return response()->json(['status' => 0, 'message' => "This slot is not available for user's address zone and given date"]);
                                    }  

                                    $cart = Cart::with('cartItems')->where('user_id', $userid)
                                        ->select('em_cart.id', 'em_cart.id as cartId', 'type', 'user_id',
                                         'em_cart.cart_total as estimate', 'user_address_id', 
                                         'job_date as date', 'job_slot as slot_id')
                                        ->get();

                                    if($cart->isNotEmpty()) {
                                        return response()->json([ 'status' => 1, 'data' => $cart, 'message' => 'Cart Items']);
                                    }   else {
                                        return response()->json([ 'status' => 0, 'data' => [], 'message' => 'No Items in Cart']);
                                    } 
                                
                                     
                                }   else {
                                    return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Not a valid Zone']);
                                }
                            }   else {
                                return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Not a valid User Address Id']);
                            }                             
                        }   else {
                            return response()->json([ 'status' => 0, 'data' => [], 'message' => 'No Items in Cart']);
                        } 
                    }   else {
                        return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid Inputs']);
                    }
                }
            }    else {
                return response()->json([ 'status' => 0, 'data' => [], 'message' => $error]);
            }
        }   catch(\Throwable $th) {
            return response()->json(['status' => 0, 'data' => [], 'message' => $th->getMessage()]);
        }  
    }


    /* Get View Cart */
    public function getViewCart(Request $request) {
        try {   
            $inputJSON = file_get_contents('php://input');

            $input = json_decode($inputJSON, TRUE);

            $requiredParams = ['user_id', 'api_token'];

            $error = $this->checkParams($input, $requiredParams, $request);

            if(empty($error)) {

                $userid = ((isset($input) && isset($input['user_id']))) ? $input['user_id'] : 0; 
                $page_no = ((isset($input) && isset($input['page_no']))) ? $input['page_no'] : 0; 

                $api_token = $request->header('x-api-key');
                $limit = CommonController::$page_limit;

                $mes = User::checkTokenExpiry($userid, $api_token);
                $status = $mes['status'];   $message = $mes['message'];
                if($status != 1) {
                    return response()->json([ 'status' => $status, 'data' => [], 'message' => $message]);
                }   else {
                    if($userid > 0) { 

                        $cart = Cart::with('cartItems')->where('user_id', $userid)
                            ->select('em_cart.id', 'em_cart.id as cartId', 'type', 'user_id',
                             'em_cart.cart_total as estimate', 'user_address_id', 
                             'job_date as date', 'job_slot as slot_id')
                            ->get();


                        if($cart->isNotEmpty()) {
                            $sub_category_id = 0;
                            if(isset($cart[0]) && isset($cart[0]->cartItems) && isset($cart[0]->cartItems[0])) {
                                $sub_category_id  = $cart[0]->cartItems[0]['sub_category_id'];
                            }

                            if($sub_category_id > 0) {
                                $additional_charge = DB::table('em_admin_settings')->where('id', 1)
                                    ->whereRaw("FIND_IN_SET(".$sub_category_id.", em_admin_settings.additional_category_ids)") 
                                    ->select('enable_additional_charges', 'additional_charges', 'additional_charge_text')
                                    ->first();

                                if(!empty($additional_charge)) {
                                    $enable_additional_charges = $additional_charge->enable_additional_charges;
                                    $additional_charges = $additional_charge->additional_charges;
                                    $additional_charge_text = $additional_charge->additional_charge_text;
                                }   else {
                                    $additional_charge = [
                                        "enable_additional_charges" => 0,
                                        "additional_charges" => 0,
                                        "additional_charge_text" => ""];
                                }
                            }   else {
                                $additional_charge = [
                                    "enable_additional_charges" => 0,
                                    "additional_charges" => 0,
                                    "additional_charge_text" => ""];
                            }

                            return response()->json([ 'status' => 1, 'data' => $cart, 'message' => 'Cart Items', 'additional_charge'=>$additional_charge]);
                        }   else {
                            return response()->json([ 'status' => 0, 'data' => [], 'message' => 'No Items in Cart', 'additional_charge'=>$additional_charge]);
                        } 
                    }   else {
                        return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid Inputs']);
                    }
                }
            }    else {
                return response()->json([ 'status' => 0, 'data' => [], 'message' => $error]);
            }
        }   catch(\Throwable $th) {
            return response()->json(['status' => 0, 'data' => [], 'message' => $th->getMessage()]);
        }  
    }

    /* Get View Cart */
    public function clearCart(Request $request) {
        try {   
            $inputJSON = file_get_contents('php://input');

            $input = json_decode($inputJSON, TRUE);

            $requiredParams = ['user_id', 'api_token'];

            $error = $this->checkParams($input, $requiredParams, $request);

            if(empty($error)) {

                $userid = ((isset($input) && isset($input['user_id']))) ? $input['user_id'] : 0; 
                $page_no = ((isset($input) && isset($input['page_no']))) ? $input['page_no'] : 0; 

                $api_token = $request->header('x-api-key');
                $limit = CommonController::$page_limit;

                $mes = User::checkTokenExpiry($userid, $api_token);
                $status = $mes['status'];   $message = $mes['message'];
                if($status != 1) {
                    return response()->json([ 'status' => $status, 'data' => [], 'message' => $message]);
                }   else {
                    if($userid > 0) { 

                        $cart_id = DB::table('em_cart')->where('user_id', $userid)->value('id');
                        if($cart_id > 0) {
                            DB::table('em_cart')->where('id', $cart_id)->delete();
                            DB::table('em_cart_services')->where('cart_id', $cart_id)->delete();
                            DB::table('em_cart_subservices')->where('cart_id', $cart_id)->delete();
                        }

                        $cart = Cart::with('cartItems')->where('user_id', $userid)
                            ->select('em_cart.id', 'em_cart.id as cartId', 'type', 'user_id',
                             'em_cart.cart_total as estimate', 'user_address_id', 
                             'job_date as date', 'job_slot as slot_id')
                            ->get();
                        if($cart->isNotEmpty()) {
                            return response()->json([ 'status' => 1, 'data' => $cart, 'message' => 'Cart Items']);
                        }   else {
                            return response()->json([ 'status' => 0, 'data' => [], 'message' => 'No Items in Cart']);
                        } 
                    }   else {
                        return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid Inputs']);
                    }
                }
            }    else {
                return response()->json([ 'status' => 0, 'data' => [], 'message' => $error]);
            }
        }   catch(\Throwable $th) {
            return response()->json(['status' => 0, 'data' => [], 'message' => $th->getMessage()]);
        }  
    }

    /* Get Days List */
    public function getDaysList(Request $request) {
        try {   
            $inputJSON = file_get_contents('php://input');

            $input = json_decode($inputJSON, TRUE);

            $requiredParams = ['user_id', 'api_token'];

            $error = $this->checkParams($input, $requiredParams, $request);

            if(empty($error)) {

                $userid = ((isset($input) && isset($input['user_id']))) ? $input['user_id'] : 0; 

                $api_token = $request->header('x-api-key');
                $limit = CommonController::$page_limit;

                $mes = User::checkTokenExpiry($userid, $api_token);
                $status = $mes['status'];   $message = $mes['message'];
                if($status != 1) {
                    return response()->json([ 'status' => $status, 'data' => [], 'message' => $message]);
                }   else {
                    if($userid > 0) { 
                        $open_days_count = DB::table('em_admin_settings')->where('id', 1)->value('open_days_count');
                        if($open_days_count > 0) { } 
                        else { 
                            $open_days_count = 15;
                        }

                        $dates = [];
                        $today_date = date('Y-m-d');
                        $dates[]['date'] = $today_date;
                        for($i=1; $i<=$open_days_count; $i++) {
                            $dates[]['date'] = date('Y-m-d', strtotime('+'.$i.'days '.$today_date));
                        }

                        if(!empty($dates)) {
                            return response()->json([ 'status' => 1, 'data' => $dates, 'message' => 'Dates List']);
                        }   else {
                            return response()->json([ 'status' => 0, 'data' => [], 'message' => 'No Items in Cart']);
                        } 
                    }   else {
                        return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid Inputs']);
                    }
                }
            }    else {
                return response()->json([ 'status' => 0, 'data' => [], 'message' => $error]);
            }
        }   catch(\Throwable $th) {
            return response()->json(['status' => 0, 'data' => [], 'message' => $th->getMessage()]);
        }  
    }

    /* Get Day Slots List */
    public function getDaySlotsList(Request $request) {
        try {   
            $inputJSON = file_get_contents('php://input');

            $input = json_decode($inputJSON, TRUE);

            $requiredParams = ['user_id', 'api_token', 'date'];

            $error = $this->checkParams($input, $requiredParams, $request);

            if(empty($error)) {

                $userid = ((isset($input) && isset($input['user_id']))) ? $input['user_id'] : 0; 
                $date = ((isset($input) && isset($input['date']))) ? $input['date'] : date('Y-m-d'); 
                $zone_id = ((isset($input) && isset($input['zone_id']))) ? $input['zone_id'] : 0;  

                $api_token = $request->header('x-api-key');

                $mes = User::checkTokenExpiry($userid, $api_token);
                $status = $mes['status'];   $message = $mes['message'];
                if($status != 1) {
                    return response()->json([ 'status' => $status, 'data' => [], 'message' => $message]);
                }   else {
                    if($userid > 0) {  

                        if($zone_id > 0) {
                            $zone = Zones::find($zone_id);
                            if(empty($zone)) {
                                return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid Zone Id']);
                            }

                            $slots = Slots::where('status', 'ACTIVE')->orderby('position', 'ASC')->get();

                            $finalslots = []; 
                            if(!empty($slots) && count($slots) > 0) {
                                $current_time = date('H:i');

                                $delivery_date = $date;

                                $timestamp = strtotime($delivery_date.' '.$current_time);
                                $newslots = []; 
                                foreach($slots as $sk=>$slot) {

                                    $timestamp_from = strtotime($slot->from_time);
                                    $timestamp_to = strtotime($slot->to_time);

                                    if($delivery_date == date("Y-m-d")) {
                                        if($timestamp_from > $timestamp) {
                                            $newslots[strtotime($delivery_date)][$timestamp_from] = $slot;
                                        }
                                    }   else if(strtotime($delivery_date) > strtotime(date("d-m-Y"))) {
                                        $newslots[strtotime($delivery_date)][$timestamp_from] = $slot;
                                    }
                                    
                                }
                                ksort($newslots);
                                foreach($newslots as $sk=>$slot) {
                                    foreach($slot as $fslot) {
                                        $finalslots[] = $fslot;
                                    }
                                }
                            }
                            
                            $servicers = DB::table('users')
                                ->leftjoin('em_service_provider', 'em_service_provider.user_id', 'users.id')
                                ->whereRaw("FIND_IN_SET(".$zone_id.", em_service_provider.zone_ids)")
                                ->where('users.status', 'ACTIVE')
                                ->where('users.approve_status', 'APPROVED')
                                ->select('users.id')->get();

                            $servicers_arr = [];
                            if($servicers->isNotEmpty()) {
                                foreach ($servicers as $key => $value) {
                                    $servicers_arr[] = $value->id;
                                }
                            }

                            foreach($finalslots as $sk=>$slot) {
                                //DB::enableQueryLog();
                                $leave_servicers = DB::table('em_servicer_leave_slots')
                                    ->whereRaw("FIND_IN_SET(".$slot->id.", em_servicer_leave_slots.Leave_slots)")
                                    ->where('leave_date', $date)
                                    ->select('servicer_id')->get();
                                //$query = DB::getQueryLog();
                                //echo "<pre>=====";   print_r($leave_servicers);
                                $leave_servicers_arr = [];
                                if($leave_servicers->isNotEmpty()) {
                                    foreach ($leave_servicers as $key => $value) {
                                        $leave_servicers_arr[] = $value->servicer_id;
                                    }

                                    $result=array_diff($servicers_arr,$leave_servicers_arr);
                                    if(count($result) == 0) {
                                        unset($finalslots[$sk]);
                                    }
                                }
                            }

                            $newids = [];
                            foreach($finalslots as $sk=>$slot) {
                                $newids[] = $slot->id;
                            }

                            if(count($newids)>0) {
                                $finalslots = Slots::where('status', 'ACTIVE')
                                    ->whereIn('id', $newids)
                                    ->orderby('position', 'ASC')->get();
                            }                               

                           // echo "<pre>dsdsds"; print_r($finalslots); exit;

                            if(!empty($finalslots)){
                                return response()->json(['status' => 1, 'message' => 'Slots List', 'details'=>$finalslots]);
                            }   else {
                                return response()->json(['status' => 0, 'message' => 'No Slots List']);
                            }   

                        }  else {
                            return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid Zone']);
                        } 
                    }   else {
                        return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid Inputs']);
                    }
                }
            }    else {
                return response()->json([ 'status' => 0, 'data' => [], 'message' => $error]);
            }
        }   catch(\Throwable $th) {
            return response()->json(['status' => 0, 'data' => [], 'message' => $th->getMessage()]);
        }  
    }


    /* Get Slots for the User */
    public function getSlots(Request $request) {
        try {   
            $inputJSON = file_get_contents('php://input');

            $input = json_decode($inputJSON, TRUE);

            $requiredParams = ['user_id', 'api_token'];

            $error = $this->checkParams($input, $requiredParams, $request);

            if(empty($error)) {

                $userid = ((isset($input) && isset($input['user_id']))) ? $input['user_id'] : 0; 
                $zone_id = ((isset($input) && isset($input['zone_id']))) ? $input['zone_id'] : 0;  

                $api_token = $request->header('x-api-key'); 

                $mes = User::checkTokenExpiry($userid, $api_token);
                $status = $mes['status'];   $message = $mes['message'];
                if($status != 1) {
                    return response()->json([ 'status' => $status, 'data' => [], 'message' => $message]);
                }   else {
                    if($userid > 0) {  
                        if($zone_id > 0) {
                            $zone = Zones::find($zone_id);
                            if(empty($zone)) {
                                return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid Zone Id']);
                            }

                            $data['status'] = 1;
                            $data['message'] = "Slots for current 3 days";

                            date_default_timezone_set('Asia/Calcutta');
                            $today_date =  date('Y-m-d');

                            $datetime = new DateTime(date('Y-m-d'));
                            $datetime->modify('+1 day');
                            $tomorrow_date =  $datetime->format('Y-m-d');

                            $datetime = new DateTime(date('Y-m-d'));
                            $datetime->modify('+2 day');
                            $dayaftertomorrow_date =  $datetime->format('Y-m-d');
                            
                            $t = date('H:i');

                            // POPULATE TODAY////////////// 
                            $slots_today = [];  $today_overall ="";    $filter = "";
                            if($slots_today == ""){
                                // $filter .= "  where s.start >= '$t'";
                            }else{
                                $filter .= "  where TIME(s.from_time) >= '$t'";
                            }

                            $overall = $this->getSlotsForTheDay($today_date, $filter, $zone_id);
                            $data['today'] = $overall;

                            $filter = '';
                            $overall_tom = $this->getSlotsForTheDay($tomorrow_date, $filter, $zone_id);
                            $data['tomorrow'] = $overall_tom;

                            $overall_daftom = $this->getSlotsForTheDay($dayaftertomorrow_date, $filter, $zone_id);
                            $data['day_after_tomorrow'] = $overall_daftom;

                            return response()->json($data);

                        }   else {
                            return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid Zone Id']);
                        }   
                    }   else {
                        return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid Inputs']);
                    }
                }
            }    else {
                return response()->json([ 'status' => 0, 'data' => [], 'message' => $error]);
            }
        }   catch(\Throwable $th) {
            return response()->json(['status' => 0, 'data' => [], 'message' => $th->getMessage()]);
        }  
    }

    public function getSlotsForTheDay($date, $filter, $zone_id) {

        $qry = "SELECT distinct(slot_id), 
                (SELECT count(emp_id) as count from em_available_slot a WHERE a.slot_id = b.slot_id AND  date='$date' AND available='YES' AND zone_id=$zone_id) as count,
                s.slot_name as name, s.period_name as period, s.position
                from em_available_slot b
            INNER JOIN em_slots s ON s.id = b.slot_id $filter ORDER BY s.position ASC";

        $slot = DB::select($qry);
        if(is_array($slot) && count($slot) > 0) {
            foreach ($slot as $key => $value) {
                $slot_id = (int) $value->slot_id;

                $bookedCount = 0; 
                $bookedalreadycheck_qry = "SELECT b.job_slot,COUNT(b.service_provider_id) as count FROM em_booking b 
                    INNER JOIN em_slots s ON b.job_slot = s.id
                    WHERE b.job_slot=$slot_id
                    AND b.job_date='$date'
                    AND b.status IN ('PLACED','ACCEPTED','RESCHEDULED')
                    AND b.payment_status = 'PENDING' group by b.job_slot";

                $bookedalreadycheck = DB::select($bookedalreadycheck_qry);
                if(is_array($bookedalreadycheck) && count($bookedalreadycheck) > 0) {
                    $bookedCount = count($bookedalreadycheck);
                }

                $resultantCount = (int) $value->count - $bookedCount;
                if ($resultantCount < 0) {
                    $resultantCount = 0;
                }

                //$slot[$key]->available = $resultantCount > 0 ? "YES" : "NO";
                $slot[$key]->available = $resultantCount > 0 ? "YES" : "YES";
            }
        }

        $overall = array("date" => $date, "slots" => $slot); 
        return $overall;
    }

    /* Post - Create General Booking */
    public function postInsertGeneralBooking(Request $request) {
        //try {   
            $inputJSON = file_get_contents('php://input');

            $input = json_decode($inputJSON, TRUE);

            $input = $request->all();

            //$requiredParams = ['user_id', 'api_token', 'user_address_id', 'slot_id', 'job_date'];

            $requiredParams = ['user_id', 'api_token'];

            $error = $this->checkParams($input, $requiredParams, $request);

            if(empty($error)) {

                $userid = ((isset($input) && isset($input['user_id']))) ? $input['user_id'] : 0; 
                $job_description = ((isset($input) && isset($input['job_description']))) ? $input['job_description'] : ''; 

                $cart_id = Cart::where('user_id', $userid)->value('id');
                if($cart_id > 0) {
                    $cart = Cart::find($cart_id);
                    /*$user_address_id = ((isset($input) && isset($input['user_address_id']))) ? $input['user_address_id'] : 0; 
                    $slot_id = ((isset($input) && isset($input['slot_id']))) ? $input['slot_id'] : 0; 
                    $job_date = ((isset($input) && isset($input['job_date']))) ? $input['job_date'] : date('Y-m-d');*/

                    $user_address_id = $cart->user_address_id; 
                    $slot_id = $cart->job_slot; 
                    $job_date = $cart->job_date;
                }   else {
                    return response()->json([ 'status' => 0, 'data' => [], 'message' => " Invalid Cart "]);
                }                

                $job_date = date('Y-m-d', strtotime($job_date));

                $api_token = $request->header('x-api-key'); 

                $mes = User::checkTokenExpiry($userid, $api_token);
                $status = $mes['status'];   $message = $mes['message'];
                if($status != 1) {
                    return response()->json([ 'status' => $status, 'data' => [], 'message' => $message]);
                }   else {
                    if($userid > 0) {  

                        if($slot_id == 0) {
                            return response()->json([ 'status' => 0, 'data' => [], 'message' => "Invalid Slot"]);
                        }   

                        $slot = Slots::find($slot_id);
                        if(empty($slot)) {
                            return response()->json([ 'status' => 0, 'data' => [], 'message' => "Invalid Slot"]);
                        }

                        $today = date('Y-m-d');
                        if (strtotime($job_date) < strtotime($today)) {
                            return response()->json([ 'status' => 0, 'data' => [], 'message' => "Given Job date is in past."]);
                        }

                        $timenow =  date('Y-m-d H:i');
                        if($slot->status != 'ACTIVE') {
                            return response()->json([ 'status' => 0, 'data' => [], 'message' => "Invalid Slot"]);
                        }
                         
                        $startNew = $job_date.' '.$slot->from_time;
                        $minutesLeftNew = strtotime($startNew) - strtotime($timenow);
                        if ($job_date==$today && $minutesLeftNew < 0) {
                            return response()->json([ 'status' => 0, 'data' => [], 'message' => "Time is in past. Invalid input"]);
                        }

                        $zone_id = UserAddress::where('id', $user_address_id)->value('zone_id');
                        $chk = $this->checkSlotAvailable($slot_id, $zone_id, $job_date);
                         
                        if($chk['status'] == 1) {
                            $updated = date('Y-m-d');

                            $cart_id = Cart::where('user_id', $userid)->value('id');
                            if($cart_id > 0) {
                                $cart = Cart::find($cart_id);
                                $type = $cart->type;
                                if($type == 1) {
                                    //$estimate = $cart->cart_total; 
                                    $estimate = $cart->sub_total; 
                                     
                                    $rescheduled_count = 0;
                                    $sub_category_id = 0;
                                    $category_id = 0;
                                    $cartitems = CartItem::where('cart_id', $cart_id)->get();
                                    if($cartitems->isNotEmpty()) {
                                        $sub_category_id = $cartitems[0]->sub_category_id;
                                        $category = Category::leftjoin('em_sub_category', 'em_sub_category.category_id', 'em_category.id')->where('em_sub_category.id', $sub_category_id)
                                            ->select('em_category.*')
                                            ->get();
                                        if($category->isNotEmpty()) {
                                            $category_id = $category[0]->id;
                                            $tax_percent = $category[0]->tax_percent;

                                            $tax_amount = ceil(($tax_percent * $estimate) / 100);

                                            $total = $estimate;
                                            $amount_payable = $total;
                                            $credits = 3;
                                            $status = 'PLACED';
                                            $payment_status = 'PENDING';

                                            $sub_total = $estimate - $tax_amount;

                                            $today_date =  date('Y-m-d');
                                            $created = $created_at = $updated_at = date('Y-m-d H:i:s'); 

                                            $today = date('ymd');
                                            $fircheck_qry = "SELECT ref_no FROM em_booking WHERE ref_no LIKE '$today%' ORDER BY id DESC LIMIT 1";
                                            $fircheck = DB::select($fircheck_qry); 
                                            if(is_array($fircheck) && count($fircheck) > 0) {
                                                $ref_no = $fircheck[0]->ref_no;
                                                $booking_ref_no = $ref_no + 1;
                                            }   else {
                                                $booking_ref_no = $today . '0001';
                                            } 

                                            $additional_charge = DB::table('em_admin_settings')->where('id', 1)
                                                ->whereRaw("FIND_IN_SET(".$sub_category_id.", em_admin_settings.additional_category_ids)") 
                                                ->select('enable_additional_charges', 'additional_charges', 'additional_charge_text') 
                                                ->first();

                                            $enable_additional_charges = 0;
                                            $additional_charges = 0;
                                            $additional_charge_text = 0;
                                            if(!empty($additional_charge)) {
                                                $enable_additional_charges = $additional_charge->enable_additional_charges;
                                                $additional_charges = $additional_charge->additional_charges;
                                                $additional_charge_text = $additional_charge->additional_charge_text;
                                            }

                                            $job_otp = CommonController::generateNumericOTP(4);

                                            $booking = new Booking();
                                            $booking->ref_no = $booking_ref_no;
                                            $booking->user_id = $userid;
                                            $booking->sub_category_id = $sub_category_id;
                                            $booking->sub_total = $sub_total;
                                            $booking->sub_total_amount = $estimate;
                                            $booking->tax_percentage = $tax_percent;
                                            $booking->tax_total = $tax_amount;
                                            $booking->total_amount = $estimate;
                                            $booking->job_date = $job_date;
                                            $booking->job_slot = $slot_id;
                                            $booking->user_address_id = $user_address_id;
                                            $booking->status = 'PLACED';
                                            $booking->payment_status = $payment_status;
                                            $booking->job_otp = $job_otp;
                                            $booking->created_at = $created_at;
                                            $booking->updated_at = $updated_at;

                                            if($enable_additional_charges == 1) {
                                                $booking->additional_charge = $additional_charges;
                                                $booking->additional_charge_text = $additional_charge_text;
                                                $booking->total_amount = $estimate + $additional_charges;
                                            }

                                            /* Job Decscription and Images */
                                            $imageslist = [];  $imageslist_str = '';
                                            $accepted_formats = ['jpeg', 'jpg', 'png'];
                                            $images = $request->file('job_images');
                                            if (!empty($images)) {
                                                foreach ($images as $key => $image) {
                                                    $ext = $image->getClientOriginalExtension();

                                                    if(!in_array($ext, $accepted_formats)) {
                                                        return response()->json(['status' => 0, 'message' => 'File Format Wrong.Please upload PNG,JPEG,JPG']);
                                                    }

                                                    $jobImage = 'services-' .rand().time() . '.' . $image->getClientOriginalExtension();

                                                    $destinationPath = public_path('/uploads/jobimages');

                                                    $image->move($destinationPath, $jobImage);

                                                    $imageslist[] = $jobImage;
                                                }
                                                if(count($imageslist)>0) {
                                                    $imageslist_str = implode(',',$imageslist);
                                                }
                                            }

                                            $booking->user_description = $job_description;
                                            $booking->user_images = $imageslist_str;

                                            $booking->save();

                                            $booking_id = $booking->id;

                                            foreach ($cartitems as $key => $value) {

                                                $sub_service_data = [
                                                    'booking_service_id' => 0,
                                                    'user_id' => $userid,
                                                    'sub_category_id' => $value->sub_category_id,
                                                    'booking_id' => $booking_id,
                                                    'service_id' => $value->service_id,
                                                    'sub_service_id' => $value->sub_service_id,
                                                    'qty' => $value->qty,
                                                    'price' => $value->price,
                                                    'amount' => $value->total_price,
                                                    'created_at' => date('Y-m-d H:i:s'),
                                                ];  


                                                DB::table('em_booking_subservices')->insertGetId($sub_service_data); 
                                            }

                                            /* Clear Cart */
                                            DB::table('em_cart')->where('id', $cart_id)->delete();
                                            DB::table('em_cart_services')->where('cart_id', $cart_id)->delete();
                                            DB::table('em_cart_subservices')->where('cart_id', $cart_id)->delete();

                                            /* Assign Employee */
                                            $emp_id = 0;
                                            $servicers_arr = $this->getZoneServicers($zone_id);

                                            $leave_servicers = DB::table('em_servicer_leave_slots')
                                                ->whereRaw("FIND_IN_SET(".$slot_id.", em_servicer_leave_slots.Leave_slots)")
                                                ->where('leave_date', $job_date)
                                                ->whereIn('servicer_id', $servicers_arr)
                                                ->select('servicer_id')->get(); 

                                            $leave_servicers_arr = [];
                                            if($leave_servicers->isNotEmpty()) {
                                                foreach ($leave_servicers as $key => $value) {
                                                    $leave_servicers_arr[] = $value->servicer_id;
                                                }
                                            }

                                            $result=array_diff($servicers_arr,$leave_servicers_arr);
                                            rsort($result);
                                            $new_arr = [];
                                            if(count($result) > 0) { 
                                                foreach($result as $rk => $rv) {
                                                    $cart_details = $this->calculateFareBreakup($userid, $rv);
                                                    if(count($cart_details) > 0){ 
                                                        $new_arr[] = $rv;
                                                        break;
                                                    }
                                                }                                                
                                                if(count($new_arr) >0) {
                                                    $emp_id = $new_arr[0];
                                                }   else {
                                                    $booking->status = 'CANCELLED';
                                                    $booking->save();

                                                    $title = "Your Booking Cancelled";
                                                    $message = "Your Booking ".$booking->ref_no." Cancelled Successfully";
                                                        
                                                    $fcmMsg = array("fcm" => array("notification" => array(
                                                            "title" => $title,
                                                            "body" => $message,
                                                            "type" => "7",
                                                          )));

                                                    CommonController::push_notification($userid, $fcmMsg);

                                                    return response()->json([ 'status' => 1, 'data' => [], 'message' => " Booking Cancelled Successfully  "]);

                                                    return response()->json([ 'status' => 0, 'data' => [], 'message' => " No Service Providers available at this time for your Services. Please try Booking after some time. "]);
                                                }
                                                

                                                $current_date = date('Y-m-d H:i:s');
                                                $convertedTime = date('Y-m-d H:i:s',strtotime('+120 seconds',strtotime($current_date)));

                                                $confirm_booking = [
                                                    'booking_id' => $booking_id,
                                                    'empl_id' => $emp_id,
                                                    'accept_status' => 0,
                                                    'notification_sent' => 2,
                                                    'notify_from_time' => $current_date,
                                                    'notify_to_time' => $convertedTime,
                                                    'booking_status' => 0,
                                                    'alert_status' => 1,
                                                    'created_at' => $current_date
                                                ];

                                                DB::table('em_confirm_booking')->insertGetId($confirm_booking); 

                                                $title = "You Have New Booking";
                                                $message = "New Booking ".$booking->ref_no."";
                                                    
                                                $fcmMsg = array("fcm" => array("notification" => array(
                                                        "title" => $title,
                                                        "body" => $message,
                                                        "type" => "2",
                                                      )));

                                                CommonController::push_notification($emp_id, $fcmMsg);

                                                $title = "New Booking Placed";
                                                $message = "New Booking ".$booking->ref_no." placed Successfully";
                                                    
                                                $fcmMsg = array("fcm" => array("notification" => array(
                                                        "title" => $title,
                                                        "body" => $message,
                                                        "type" => "2",
                                                      )));

                                                CommonController::push_notification($userid, $fcmMsg);

                                                return response()->json([ 'status' => 1, 'data' => [], 'message' => " Booking Placed Successfully  "]);

                                            }   else {
                                                $booking->status = 'CANCELLED';
                                                $booking->save();

                                                $title = "Your Booking Cancelled";
                                                $message = "Your Booking ".$booking->ref_no." Cancelled Successfully";
                                                    
                                                $fcmMsg = array("fcm" => array("notification" => array(
                                                        "title" => $title,
                                                        "body" => $message,
                                                        "type" => "7",
                                                      )));

                                                CommonController::push_notification($userid, $fcmMsg);

                                                return response()->json([ 'status' => 1, 'data' => [], 'message' => " Booking Cancelled Successfully  "]);

                                                return response()->json([ 'status' => 0, 'data' => [], 'message' => " No Service Providers available at this time for your Services. Please try Booking after some time. "]);
                                            }
                                        }
                                    }  
                                }
                            }   else {
                                return response()->json([ 'status' => 0, 'data' => [], 'message' => " Invalid Cart "]);
                            }
                        }   else {
                            return response()->json($chk);
                        } 
                    }   else {
                        return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid Inputs']);
                    }
                }
            }    else {
                return response()->json([ 'status' => 0, 'data' => [], 'message' => $error]);
            }
        /*}   catch(\Throwable $th) {
            return response()->json(['status' => 0, 'data' => [], 'message' => $th->getMessage()]);
        }  */
    }

    public function calculateFareBreakup($userid, $service_user_id, $booking_true=false, $booking_id=0) {
        if(!$booking_true) {
            $cartsubservices = UserCartSubServices::where('user_id', $userid)
            ->select(DB::RAW('GROUP_CONCAT(sub_service_id) as usersubservices'), DB::RAW('GROUP_CONCAT(service_id) as userservices'))->first(); 
        } else {
            $cartsubservices = BookingSubServices::where('booking_id', $booking_id)
            ->select(DB::RAW('GROUP_CONCAT(sub_service_id) as usersubservices'), DB::RAW('GROUP_CONCAT(service_id) as userservices'))->first();
        }  
        $servicer_services = []; $cart_details = [];
        if(!empty($cartsubservices)) {
            $usersubservices = $cartsubservices->usersubservices;
            if(!empty($usersubservices)) {
                $user_subservices = explode(',', $usersubservices);
                $user_subservices = array_unique($user_subservices);

                $userservices = $cartsubservices->userservices;
                $userservices = explode(',', $userservices);
                $userservices = array_unique($userservices);

                $servicer_services_arr = DB::table('em_service_provider')
                    ->where('user_id', $service_user_id)->value('service_ids');
                $servicer_sub_services_arr = [];
                if(!empty($servicer_services_arr)) {
                    $servicer_services_arr = explode(',', $servicer_services_arr);
                    if(count($servicer_services_arr)>0) {
                        $servicer_sub_services = DB::table('em_sub_service')
                            ->whereIn('service_id', $servicer_services_arr)
                            ->select('id')->get();
                        if($servicer_sub_services->isNotEmpty()) {
                            foreach($servicer_sub_services as $ssk => $ssv) {
                                $servicer_sub_services_arr[] = $ssv->id;
                            }
                        }

                    }
                }

                if(count($servicer_sub_services_arr)>0) {
                    $servicer_service_details = SubServices::whereIN('id', $servicer_sub_services_arr)->get();

                    foreach ($servicer_service_details as $sdkey => $sdvalue) {
                        $servicer_services[$sdvalue['id']] = $sdvalue;
                    }
                }

                /*if(count($user_subservices)>0) {
                    $servicer_service_details = ServicerServiceDetails::where('service_user_id', $service_user_id)->whereIN('sub_service_id', $user_subservices)->get()->toArray();

                    foreach ($servicer_service_details as $sdkey => $sdvalue) {
                        $servicer_services[$sdvalue['sub_service_id']] = $sdvalue;
                    }
                }*/
            }
            /*if(!$booking_true) {
                $cartservices = UserCartServices::with('cartSubServices')->whereIn('user_id', $userid)->get()->toArray();
            } else {
                $cartservices = BookingServices::with('bookSubServices')->where('booking_id', $booking_id)->get()->toArray();
            }*/
            if(!$booking_true) {
                $cartsubservicess = UserCartSubServices::where('user_id', $userid)->get()->toArray();
            } else {
                $cartsubservicess = BookingSubServices::where('booking_id', $booking_id)->get()->toArray();
            }
            $cart_total = 0;  $service_total = 0;  //  echo "<pre>";print_r($user_subservices);  print_r($cartservices); exit;
            /*foreach ($cartservices as $cskey => $csvalue) {
                
                if(!$booking_true) {
                    $cartsubservicess = $cartservices[$cskey]['cart_sub_services'];
                }   else {
                    $cartsubservicess = $cartservices[$cskey]['book_sub_services'];
                }*/
                 
                foreach ($cartsubservicess as $csdkey => $csdvalue) {
                    if(isset($servicer_services[$csdvalue['sub_service_id']])) {
                        $service_qty = 1; //$csvalue['qty'];   
                        //if($csvalue['service_based_on'] == 1) {   // Hour Based
                            // amount = no of fan * celling fan (repair) * price of service
                            
                            $sub_service_qty = $csdvalue['qty'];
                            //$price = $servicer_services[$csdvalue['sub_service_id']]['hour_price'];
                            $price = $servicer_services[$csdvalue['sub_service_id']]['price'];
                            $subtotal = $service_qty * $sub_service_qty * $price;
                            //echo $service_qty .'*'. $sub_service_qty .'*'. $price."==";

                            $cartsubservicess[$csdkey]['quantity'] = $service_qty;
                            $cartsubservicess[$csdkey]['hour'] = $sub_service_qty;

                        //}   else {  // Fixed Price
                            // amount = celling fan (repair) * price of service
                            $sub_service_qty = $csdvalue['qty'];
                            //$price = $servicer_services[$csdvalue['sub_service_id']]['fixed_price'];
                            $price = $servicer_services[$csdvalue['sub_service_id']]['price'];
                            $subtotal = $sub_service_qty * $price;
                            //echo  $sub_service_qty .'*'. $price."==";
                            $cartsubservicess[$csdkey]['quantity'] = $sub_service_qty;
                            $cartsubservicess[$csdkey]['hour'] = 0;
                        //}
                        
                        $cartsubservicess[$csdkey]['price'] = $price;
                        $cartsubservicess[$csdkey]['subtotal'] = $subtotal;

                        $service_total += $subtotal;
                        $cart_total += $subtotal;
                    }   else {
                        $dummy = [];
                        return $dummy;
                    }

                    /*if(!$booking_true) {
                        $cartservices[$cskey]['cart_sub_services'] = $cartsubservicess;
                    }   else {
                        $cartservices[$cskey]['book_sub_services'] = $cartsubservicess;
                    }*/

                }  
                //$cartservices[$cskey]['service_total'] = $service_total;
           // }
            $cart_details['cart_services'] = '';//$cartservices;
            $cart_details['cart_total'] = $cart_total;
        }
        //echo "<pre>"; print_r($cart_details); exit;
        return $cart_details;
    }

    public function checkZoneId($zone_id) {
        $zone = Zones::find($zone_id);
        if(empty($zone))  {
            return false;
        }
        return true;
    }


    public function checkSlotAvailable($slot_id, $zone_id, $date) {

        $zone = $this->checkZoneId($zone_id);
        if($zone) {
            $servicers_arr = $this->getZoneServicers($zone_id);
                        
            $leave_servicers = DB::table('em_servicer_leave_slots')
                ->whereRaw("FIND_IN_SET(".$slot_id.", em_servicer_leave_slots.Leave_slots)")
                ->where('leave_date', $date)
                ->whereIn('servicer_id', $servicers_arr)
                ->select('servicer_id')->get(); 

            $leave_servicers_arr = [];
            if($leave_servicers->isNotEmpty()) {
                foreach ($leave_servicers as $key => $value) {
                    $leave_servicers_arr[] = $value->servicer_id;
                }
            }

            $result=array_diff($servicers_arr,$leave_servicers_arr);

            if(count($result)>0) {
                $bookedalreadycheck_qry = "SELECT b.job_slot,COUNT(b.service_provider_id) as count FROM em_booking b 
                    INNER JOIN em_slots s ON b.job_slot = s.id
                    WHERE b.job_slot=$slot_id
                    AND b.job_date='$date'
                    AND b.status IN ('PLACED','ACCEPTED','RESCHEDULED')
                    AND b.payment_status = 'PENDING' group by b.job_slot";

                $bookedCount = 0;
                $bookedalreadycheck = DB::select($bookedalreadycheck_qry);
                if(is_array($bookedalreadycheck) && count($bookedalreadycheck) > 0) {
                    $bookedCount = count($bookedalreadycheck);
                } 

                $counts_per_slot = 1;
                $slot = Slots::find($slot_id);
                if(!empty($slot)) {
                    $counts_per_slot = $slot->counts_per_slot;
                }

                if ($bookedCount > $counts_per_slot) { 
                    return ['status' => 0, 'data' => [], 'message' => 'Slot is Already Booked'];
                } 
            } 

        }   else {
            return ['status' => 0, 'data' => [], 'message' => 'Zone is not available'];
        }
        return ['status' => 1, 'data' => [], 'message' => ''];
        /*$zone = $this->checkZoneId($zone_id);
        if($zone) {
            $qry = "SELECT distinct(slot_id), 
                (SELECT count(emp_id) as count from em_available_slot a WHERE a.slot_id = b.slot_id AND  date='$date' AND available='YES' AND zone_id=$zone_id) as count,
                s.slot_name as name, s.period_name as period, s.position
                from em_available_slot b
                INNER JOIN em_slots s ON s.id = b.slot_id WHERE s.id = $slot_id";

            $slot = DB::select($qry);
            if(is_array($slot) && count($slot) > 0) {
                $bookedalreadycheck_qry = "SELECT b.job_slot,COUNT(b.service_provider_id) as count FROM em_booking b 
                    INNER JOIN em_slots s ON b.job_slot = s.id
                    WHERE b.job_slot=$slot_id
                    AND b.job_date='$date'
                    AND b.status IN ('PLACED','ACCEPTED','RESCHEDULED')
                    AND b.payment_status = 'PENDING' group by b.job_slot";

                $bookedCount = 0;
                $bookedalreadycheck = DB::select($bookedalreadycheck_qry);
                if(is_array($bookedalreadycheck) && count($bookedalreadycheck) > 0) {
                    $bookedCount = count($bookedalreadycheck);
                } 
                if ($bookedCount > 0) { 
                    return ['status' => 0, 'data' => [], 'message' => 'Slot is Already Booked'];
                }  
            }   else {
                return ['status' => 0, 'data' => [], 'message' => 'Slot is not available'];
            }
        }   else {
            return ['status' => 0, 'data' => [], 'message' => 'Zone is not available'];
        }
        return ['status' => 1, 'data' => [], 'message' => ''];  */ 
    }

    /* Get User Bookings */
    public function getUserBookings(Request $request) {
        try {   
            $inputJSON = file_get_contents('php://input');

            $input = json_decode($inputJSON, TRUE);

            $requiredParams = ['user_id', 'api_token'];

            $error = $this->checkParams($input, $requiredParams, $request);

            if(empty($error)) {

                $userid = ((isset($input) && isset($input['user_id']))) ? $input['user_id'] : 0; 
                $page_no = ((isset($input) && isset($input['page_no']))) ? $input['page_no'] : 0; 

                $api_token = $request->header('x-api-key');
                $limit = CommonController::$page_limit;

                $mes = User::checkTokenExpiry($userid, $api_token);
                $status = $mes['status'];   $message = $mes['message'];
                if($status != 1) {
                    return response()->json([ 'status' => $status, 'data' => [], 'message' => $message]);
                }   else {
                    if($userid > 0) { 
                        //DB::RAW('group_concat(em_slots.slot_name) as name')
                        $bookings = Booking::select('em_booking.*','em_slots.slot_name as name', DB::RAW('(SELECT name FROM em_sub_category mcc WHERE mcc.id = em_booking.`sub_category_id`) AS main_category_name'))
                            ->leftjoin('em_slots', \DB::raw("FIND_IN_SET(em_slots.id, em_booking.job_slot)"),">",\DB::raw("'0'"))
                            ->where('em_booking.user_id', $userid)
                           // ->groupby('em_booking.id')
                            ->orderby('em_booking.id','DESC')
                            ->skip($page_no)->limit($limit)
                            ->get();
                        
                        if($bookings->isNotEmpty()) {
                            /*$bookings = $bookings->toArray();
                            foreach ($bookings as $key => $bookingrows) {
                                 $bookingsArray[] = array(
                                    "type" => 1,
                                    "booking_id" => (int) $bookingrows['id'],
                                    "booking_ref_no" => $bookingrows['ref_no'],
                                    "estimate" => (int) $bookingrows['total_amount'],
                                    "date" => $bookingrows['job_date'],
                                    "slot" => $bookingrows['name'],
                                    "booking_status" => $bookingrows['status'],
                                    "servicer_comment" => '', //$bookingrows['servicer_comment'],
                                    "servicer_status" => '', //$bookingrows['servicer_status'],
                                    "payment" => $bookingrows['payment_status'],
                                    "payment_type" => $bookingrows['payment_mode'],
                                    "updated" => $bookingrows['updated_at'],
                                    "amount_payable" => $bookingrows['total_amount'],
                                    "hour_type" => '', //$bookingrows['hour_type'],
                                    "main_category_name" => $bookingrows['main_category_name'],
                                    
                                );
                            }*/
                         
                            return response()->json([ 'status' => 1, 'data' => $bookings, 'message' => 'Bookings List']);
                        }   else {
                            return response()->json([ 'status' => 0, 'data' => [], 'message' => 'No Bookings']);
                        } 
                    }   else {
                        return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid Inputs']);
                    }
                }
            }    else {
                return response()->json([ 'status' => 0, 'data' => [], 'message' => $error]);
            }
        }   catch(\Throwable $th) {
            return response()->json(['status' => 0, 'data' => [], 'message' => $th->getMessage()]);
        }  
    }

    /* Get User Booking Detail */
    public function getUserBookingDetails(Request $request) {
        try {   
            $inputJSON = file_get_contents('php://input');

            $input = json_decode($inputJSON, TRUE);

            $requiredParams = ['user_id', 'api_token', 'booking_id'];

            $error = $this->checkParams($input, $requiredParams, $request);

            if(empty($error)) {

                $userid = ((isset($input) && isset($input['user_id']))) ? $input['user_id'] : 0; 
                $booking_id = ((isset($input) && isset($input['booking_id']))) ? $input['booking_id'] : 0; 

                $api_token = $request->header('x-api-key');
                $limit = CommonController::$page_limit;

                $mes = User::checkTokenExpiry($userid, $api_token);
                $status = $mes['status'];   $message = $mes['message'];
                if($status != 1) {
                    return response()->json([ 'status' => $status, 'data' => [], 'message' => $message]);
                }   else {
                    if($userid > 0 && $booking_id>0) { 
                        //DB::RAW('group_concat(em_slots.slot_name) as name')
                        $bookings = Booking::select('em_booking.*','em_slots.slot_name as name', DB::RAW('(SELECT name FROM em_sub_category mcc WHERE mcc.id = em_booking.`sub_category_id`) AS main_category_name'))
                            ->leftjoin('em_slots', \DB::raw("FIND_IN_SET(em_slots.id, em_booking.job_slot)"),">",\DB::raw("'0'"))
                            ->where('em_booking.user_id', $userid)
                            ->where('em_booking.id', $booking_id) 
                            ->get();
                        
                        if($bookings->isNotEmpty()) {
                            $bookings = $bookings->toArray();
                            foreach ($bookings as $key => $bookingrows) {
                                 $bookingsArray[] = array(
                                    "type" => 1,
                                    "booking_id" => (int) $bookingrows['id'],
                                    "booking_ref_no" => $bookingrows['ref_no'],
                                    "estimate" => (int) $bookingrows['total_amount'],
                                    "date" => $bookingrows['job_date'],
                                    "slot" => $bookingrows['name'],
                                    "booking_status" => $bookingrows['status'],
                                    "servicer_comment" => '', //$bookingrows['servicer_comment'],
                                    "servicer_status" => '', //$bookingrows['servicer_status'],
                                    "payment" => $bookingrows['payment_status'],
                                    "payment_type" => $bookingrows['payment_mode'],
                                    "updated" => $bookingrows['updated_at'],
                                    "amount_payable" => $bookingrows['total_amount'],
                                    "hour_type" => '', //$bookingrows['hour_type'],
                                    "main_category_name" => $bookingrows['main_category_name'],
                                    
                                );
                            }

                            $user_bookings = Booking::with('bookItems')
                                ->where('user_id', $userid)
                                ->where('id', $booking_id)
                                ->first();
                         
                            return response()->json([ 'status' => 1, 'data' => $bookingsArray, 'message' => 'Bookings List', 'details' => $user_bookings]);
                        }   else {
                            return response()->json([ 'status' => 0, 'data' => [], 'message' => 'No Bookings']);
                        } 
                    }   else {
                        return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid Inputs']);
                    }
                }
            }    else {
                return response()->json([ 'status' => 0, 'data' => [], 'message' => $error]);
            }
        }   catch(\Throwable $th) {
            return response()->json(['status' => 0, 'data' => [], 'message' => $th->getMessage()]);
        }  
    }

    // User Accept Booking
    public function postUserBookingDecline(Request $request)
    {
        try {   
            $inputJSON = file_get_contents('php://input');

            $input = json_decode($inputJSON, TRUE);

            $requiredParams = ['user_id', 'api_token', 'booking_id', 'cancelled_reason'];

            $error = $this->checkParams($input, $requiredParams, $request);

            if(empty($error)) {

                $userid = ((isset($input) && isset($input['user_id']))) ? $input['user_id'] : 0;  
                $booking_id = ((isset($input) && isset($input['booking_id']))) ? $input['booking_id'] : 0;
                $cancelled_reason = ((isset($input) && isset($input['cancelled_reason']))) ? $input['cancelled_reason'] : '';

                $api_token = $request->header('x-api-key');
                $limit = CommonController::$page_limit;

                $mes = User::checkTokenExpiry($userid, $api_token);
                $status = $mes['status'];   $message = $mes['message'];
                if($status != 1) {
                    return response()->json([ 'status' => $status, 'data' => [], 'message' => $message]);
                }   else {
                    if($userid > 0 && $booking_id > 0) {  
                         Booking::where('user_id', $userid)
                            ->where('id', $booking_id)
                            ->update(['status'=>'CANCELLED', 'customer_cancelled_reason' =>$cancelled_reason, 
                                'cancelled_date' => date('Y-m-d H:i:s'), 'cancelled_by'=>'USER']);

                        DB::table('em_booking_trackings')->insert([
                            'booking_id' => $booking_id,
                            'job_status' => 'CANCELLED',
                            'job_status_value' => 'CANCELLED BY USER',
                            'status_updated_date' => date('Y-m-d H:i:s'),
                            'created_at' => date('Y-m-d H:i:s')
                        ]);
                        return response()->json(['status' => 1, 'message' => 'Booking Declined']);
                    }   else {
                        return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid Inputs']);
                    }
                }
            }    else {
                return response()->json([ 'status' => 0, 'data' => [], 'message' => $error]);
            }
        }   catch(\Throwable $th) {
            return response()->json(['status' => 0, 'data' => [], 'message' => $th->getMessage()]);
        } 
    }

    /* Get Zones List */
    public function getZones(Request $request) {
        try {   
            $inputJSON = file_get_contents('php://input');

            $input = json_decode($inputJSON, TRUE);

            $requiredParams = ['user_id', 'api_token'];

            $error = $this->checkParams($input, $requiredParams, $request);

            if(empty($error)) {

                $userid = ((isset($input) && isset($input['user_id']))) ? $input['user_id'] : 0;  
                $page_no = ((isset($input) && isset($input['page_no']))) ? $input['page_no'] : 0;  
                $search = ((isset($input) && isset($input['search']))) ? $input['search'] : '';

                $api_token = $request->header('x-api-key');
                $limit = CommonController::$page_limit;

                $mes = User::checkTokenExpiry($userid, $api_token);
                $status = $mes['status'];   $message = $mes['message'];
                if($status != 1) {
                    return response()->json([ 'status' => $status, 'data' => [], 'message' => $message]);
                }   else {
                    if($userid > 0) {  

                        $zonesqry = Zones::where('status', 'ACTIVE');

                        $keywords = []; $keystr = '';
                        if(!empty($search)) {
                            $keywords = explode(" ", $search); 
                        }

                        $searcharr = []; $searchsql = '';
                        if(count($keywords)>0) { 
                            foreach ($keywords as $kkey => $kvalue) {
                                $zonesqry->where(function($query) use($kvalue) {
                                        $query->where('zone_name', 'LIKE', $kvalue.'%')
                                            ->orwhere('zone_name', 'LIKE', '% '.$kvalue.'%');
                                    });
                                //$zonesqry->where("zone_name', 'LIKE', '$kvalue%' OR zone_name like '% $kvalue%')";
                            } 
                        }

                        $zones = $zonesqry->skip($page_no)->take($limit)->get();
                        
                        if($zones->isNotEmpty()) {                         
                            return response()->json([ 'status' => 1, 'data' => $zones, 'message' => 'Zones List']);
                        }   else {
                            return response()->json([ 'status' => 0, 'data' => [], 'message' => 'No Zones']);
                        } 
                    }   else {
                        return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid Inputs']);
                    }
                }
            }    else {
                return response()->json([ 'status' => 0, 'data' => [], 'message' => $error]);
            }
        }   catch(\Throwable $th) {
            return response()->json(['status' => 0, 'data' => [], 'message' => $th->getMessage()]);
        }  
    }

    /* Get Banks List */
    public function getBanks(Request $request) {
        try {   
            $inputJSON = file_get_contents('php://input');

            $input = json_decode($inputJSON, TRUE);

            $requiredParams = ['user_id', 'api_token'];

            $error = $this->checkParams($input, $requiredParams, $request);

            if(empty($error)) {

                $userid = ((isset($input) && isset($input['user_id']))) ? $input['user_id'] : 0;  

                $api_token = $request->header('x-api-key');
                $limit = CommonController::$page_limit;

                $mes = User::checkTokenExpiry($userid, $api_token);
                $status = $mes['status'];   $message = $mes['message'];
                if($status != 1) {
                    return response()->json([ 'status' => $status, 'data' => [], 'message' => $message]);
                }   else {
                    if($userid > 0) {  
                        $banks = Banks::where('status', 'ACTIVE')->get();
                        
                        if($banks->isNotEmpty()) {                         
                            return response()->json([ 'status' => 1, 'data' => $banks, 'message' => 'Banks List']);
                        }   else {
                            return response()->json([ 'status' => 0, 'data' => [], 'message' => 'No Banks']);
                        } 
                    }   else {
                        return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid Inputs']);
                    }
                }
            }    else {
                return response()->json([ 'status' => 0, 'data' => [], 'message' => $error]);
            }
        }   catch(\Throwable $th) {
            return response()->json(['status' => 0, 'data' => [], 'message' => $th->getMessage()]);
        }  
    }


    /* Servicer Functionalities */

    
    /* Post Servicer Register / Login */
    public function postServicer(Request $request) {
        try {   
            $inputJSON = file_get_contents('php://input');

            $input = json_decode($inputJSON, TRUE);

            $requiredParams = ['otp', 'country_code', 'mobile', 'device_type', 'device_id', 'fcm_token'];

            $error = $this->checkParams($input, $requiredParams, $request);

            if(empty($error)) {
                $otp = isset($input['otp']) ? $input['otp'] : '';

                $country_code = $input['country_code']; 
                $chkcountrycode = $this->checkValidCountryCode($country_code);

                if(!empty($chkcountrycode)) {
                    return response()->json([ 'status' => 0, 'message' => $chkcountrycode]);
                }

                $country = Countries::where('status', 'ACTIVE')->where('phonecode', $country_code)->value('id');

                $mobile = $input['mobile'];
                $device_type = $input['device_type'];
                $device_id = $input['device_id'];
                $fcm_id = $input['fcm_token']; 

                // Mobile number must not be start with 0
                if(substr( $mobile, 0, 1 ) === "0") {
                    return response()->json(['status' => 0, 'data' => [], 'message' => 'Invalid Mobile']);
                }

                $mobileEx = DB::table('users')->where('mobile', $mobile)->where('user_type', 'SERVICEPROVIDER')->first();
                if(!empty($mobileEx)) {  // registered user
                    if($mobileEx->status != 'ACTIVE') {
                        return response()->json(['status' => 0, 'data' => [], 'message' => 'Your Account has been Blocked']);
                    }   elseif($mobileEx->user_type != 'SERVICEPROVIDER') {
                        return response()->json(['status' => 0, 'data' => [], 'message' => 'Mobile Number Already Exists']);
                    } else {

                        $date = date('Y-m-d H:i:s');
                        DB::table('users')->where('fcm_id', $fcm_id)->update(['fcm_id' => '']);
                        // Check for Expiry
                        $expiry = $mobileEx->api_token_expiry;

                        $user = User::where('id', $mobileEx->id)->where('user_type', 'SERVICEPROVIDER')->get();
                        if($user->isNotEmpty()) {
                            $user = $user[0];
                        }

                        //if($expiry <= date('Y-m-d H:i:s')) {

                            $def_expiry_after =  CommonController::getDefExpiry();
                            $user->api_token_expiry = date('Y-m-d H:i:s', strtotime('+'.$def_expiry_after.' months'. $date));
                            //$user->api_token = User::random_strings(30);
                            $user->save();
                        //}
                        
                        $ex_fcm_id = $user->fcm_id;

                        $user->otp = $otp;
                        if(empty($otp)) {
                            CommonController::otpSend($user->id);
                            $user->is_otp_verified = 0;
                        }   else {
                            $user->otp = $otp;
                            $user->is_otp_verified = 1;
                        }

                        $user->fcm_id = $fcm_id;
                        $user->last_login_date = date('Y-m-d H:i:s');
                        $user->last_app_opened_date = date('Y-m-d H:i:s');
                        $user->save();

                        //CommonController::otpSend($mobileEx->id);

                        /* Check and update and logout the session if current login from different device */

                        $atotherdevice = DB::table('em_users_loginstatus')
                            ->where('user_id', $mobileEx->id)
                            ->orderby('id', 'desc')
                            ->first();

                        if(!empty($atotherdevice)) {
                            $ex_device_id = $atotherdevice->device_id;
                            if($ex_device_id != $device_id) {
                                /* Send notification to the previous device of the user */
                                $user->api_token = User::random_strings(30);
                                $user->save();

                                $title = ' Login on Different Device ';
                                $message = 'Last login is on Different Device. So Logout and Login Again.';
                                $fcmMsg = array("fcm" => array("notification" => array(
                                    "title" => $title,
                                    "body" => $message,
                                    "type" => "1",
                                  )));

                                CommonController::push_notification($user->id, $fcmMsg, 0, $ex_fcm_id);
                            }
                        }

                        $atotherdevice = DB::table('em_users_loginstatus')
                            ->where('user_id', $mobileEx->id)
                            ->where('device_id', '!=', $device_id)
                            ->count();

                        if($atotherdevice > 0) {
                            DB::table('em_users_loginstatus')
                            ->where('user_id', $mobileEx->id)
                            ->where('device_id', '!=', $device_id)
                            ->update(['api_token_expiry'=> $mobileEx->api_token_expiry, 'updated_at'=>date('Y-m-d H:i:s')]);
                        }

                        DB::table('em_users_loginstatus')->insert([
                            'user_id' => $mobileEx->id,
                            'fcm_id' => $fcm_id,
                            'device_id' => $device_id,
                            'device_type' => $device_type,
                            'api_token_expiry' => $mobileEx->api_token_expiry,
                            'created_at' => date('Y-m-d H:i:s'),
                        ]);
                        $user = User::find($mobileEx->id);
                        $is_register_complete = $user->is_register_complete;

                       // CommonController::auth($mobileEx->id);

                        $user = CommonController::getUserDetails($user->id);

                        DB::table('em_otp')->where('otp', $otp)->where('cell', $mobile)
                            ->where('country_code', $country_code)->delete(); 
                        if($is_register_complete == 0) {
                            return response()->json(['status' => 2, 'message' => 'Login Successful. Update Profile', 'data' => $user]);
                        }   else {
                            return response()->json(['status' => 1, 'message' => 'Login Successful.', 'data' => $user]);
                        }
                    }
                }   else {  // new user
                    $today = date('ymd');
                    $fircheck_qry = "SELECT reg_no FROM users WHERE reg_no LIKE '$today%' ORDER BY id DESC LIMIT 1";
                    $fircheck = DB::select($fircheck_qry); 
                    if(is_array($fircheck) && count($fircheck) > 0) {
                        $reg_no = $fircheck[0]->reg_no;
                        $reg_no = $reg_no + 1;
                    }   else {
                        $reg_no = $today . '0001';
                    } 

                    $date = date('Y-m-d H:i:s');
                    $user = new User();
                    $user->reg_no = $reg_no;
                    $user->mobile = $mobile;
                    $user->country = $country;
                    $user->country_code = $country_code;
                    $user->code_mobile = $country_code.$mobile;
                    $user->fcm_id = $fcm_id;
                    $user->user_type = 'SERVICEPROVIDER';
                    $user->status = 'ACTIVE';
                    $user->otp = $otp;
                    $referral_code = User::random_strings(5);
                    $user->referal_code = $referral_code;
                    $user->joined_date = date('Y-m-d H:i:s');

                    $user->last_login_date = $date;
                    $user->last_app_opened_date = $date;
                    $user->user_source_from = $device_type;
                    $user->api_token = User::random_strings(30);

                    $def_expiry_after =  CommonController::getDefExpiry();

                    $user->api_token_expiry = date('Y-m-d H:i:s', strtotime('+'.$def_expiry_after.' months'. $date));
                    $user->created_at = $date;
                    $user->referral_code = User::random_strings(5);

                    $user->wallet_amount = 0;
                    $user->gender = 'MALE';
                    $user->save();

                    $userid = $user->id;

                    if(empty($otp)) {
                        $user->is_otp_verified = 0;
                        CommonController::otpSend($user->id);
                    }   else {
                        $user->otp = $otp;
                        $user->is_otp_verified = 1;
                    }

                    $user->save();

                    DB::table('em_users_loginstatus')->insert([
                        'user_id' => $user->id,
                        'fcm_id' => $fcm_id,
                        'device_id' => $device_id,
                        'device_type' => $device_type,
                        'api_token_expiry' => $user->api_token_expiry,
                        'status' => 'LOGIN',
                        'created_at' => date('Y-m-d H:i:s'),
                    ]);

                    DB::table('users_active_status')->insert([
                        'user_id' => $user->id,
                        'status' => 'INACTIVE',
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);             

                    $servicer = new ServiceProvider();
 
                    $servicer->user_id = $user->id;

                    $servicer->save();       

                    $user = CommonController::getUserDetails($user->id);

                    DB::table('em_otp')->where('otp', $otp)->where('cell', $mobile)
                            ->where('country_code', $country_code)->delete(); 

                    if (!empty($user)) {

                        return response()->json(['status' => 2, 'message' => 'Successfully Registered.', 'data' => $user]);

                    } else {

                        return response()->json(['status' => 0, 'data' => [], 'message' => 'Registration Failed']);
                    }
                }
            }    else {
                return response()->json([ 'status' => 0, 'data' => [], 'message' => $error]);
            }
        }   catch(\Throwable $th) {
            return response()->json(['status' => 0, 'data' => [], 'message' => $th->getMessage()]);
        }  
    }

    /* Post Servicer Update the Address and his personal details */
    public function postServicerUpdateStep1(Request $request) {
        try {   
            $inputJSON = file_get_contents('php://input');

            $input = json_decode($inputJSON, TRUE);

            $requiredParams = ['user_id', 'api_token', 'name', 'email', 'code', 'cell', 'dob', 'gender', 'house', 'locality', 'city', 'pincode'];

            $error = $this->checkParams($input, $requiredParams, $request);

            if(empty($error)) {

                $userid = ((isset($input) && isset($input['user_id']))) ? $input['user_id'] : 0;  

                $api_token = $request->header('x-api-key');
                $limit = CommonController::$page_limit;

                $mes = User::checkTokenExpiry($userid, $api_token);
                $status = $mes['status'];   $message = $mes['message'];
                if($status != 1) {
                    return response()->json([ 'status' => $status, 'data' => [], 'message' => $message]);
                }   else {
                    if($userid > 0) { 

                        $name = ((isset($input) && isset($input['name']))) ? $input['name'] : '';  
                        $email = ((isset($input) && isset($input['email']))) ? $input['email'] : '';  
                        $code = ((isset($input) && isset($input['code']))) ? $input['code'] : '';  
                        $cell = ((isset($input) && isset($input['cell']))) ? $input['cell'] : '';  
                        $dob = ((isset($input) && isset($input['dob']))) ? $input['dob'] : '';  
                        $gender = ((isset($input) && isset($input['gender']))) ? $input['gender'] : 'MALE';  
                        $house = ((isset($input) && isset($input['house']))) ? $input['house'] : '';  
                        $locality = ((isset($input) && isset($input['locality']))) ? $input['locality'] : '';  
                        $city = ((isset($input) && isset($input['city']))) ? $input['city'] : '';  
                        $pincode = ((isset($input) && isset($input['pincode']))) ? $input['pincode'] : '';  
                        $experience = ((isset($input) && isset($input['experience']))) ? $input['experience'] : '';  
                        $experience_desc = ((isset($input) && isset($input['experience_desc']))) ? $input['experience_desc'] : '';  
                        
                        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                            return response()->json([ 'status' => 0, 'data' => [], 'message' => "Invalid email format"]);
                        }

                        if(empty($name) || empty($email)) {
                            return response()->json([ 'status' => 0, 'data' => [], 'message' => "Invalid Name and Email"]);  
                        }

                        if(!empty($dob)){
                            $dob = date('Y-m-d', strtotime($dob));
                        }

                        $is_register_complete = User::where('id', $userid)->value('is_register_complete');
                        $step = User::where('id', $userid)->value('step');
                        if($is_register_complete < 1) {
                            $is_register_complete = 1;
                            $step = 1;
                        }   

                        User::where('id', $userid)
                            ->update([
                                'name' => $name, 
                                'email' => $email, 
                                'gender' => $gender,
                                'dob' => $dob,
                                'is_register_complete' => $is_register_complete,
                                'step' => $step
                            ]);

                        ServiceProvider::where('user_id', $userid)
                            ->update([
                                'house' => $house, 
                                'locality' => $locality, 
                                'city' => $city,
                                'pincode' => $pincode,
                                'experience' => $experience,
                                'experience_description' => $experience_desc
                            ]);

                        $user = CommonController::getUserDetails($userid);
 
                        if (!empty($user)) {

                            return response()->json(['status' => 1, 'message' => 'Step 1 Details updated successfully', 'data' => $user]);

                        } else {

                            return response()->json(['status' => 0, 'data' => [], 'message' => 'Updation Failed']);
                        }

                    }   else {
                        return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid Inputs']);
                    }
                }
            }    else {
                return response()->json([ 'status' => 0, 'data' => [], 'message' => $error]);
            }
        }   catch(\Throwable $th) {
            return response()->json(['status' => 0, 'data' => [], 'message' => $th->getMessage()]);
        }  
    }

    /* Post Servicer Update the Address Proof details */
    public function postServicerUpdateStep2(Request $request) {

        try {   
            $inputJSON = file_get_contents('php://input');

            $input = json_decode($inputJSON, TRUE);

            $input = $request->all();

            $requiredParams = ['user_id', 'api_token', 'id_proof_front', 'id_proof_back', 
                'driving_license_front', 'driving_license_back', 'noc_front'];

            $error = $this->checkParams($input, $requiredParams, $request, true);

            if(empty($error)) {

                $userid = ((isset($input) && isset($input['user_id']))) ? $input['user_id'] : 0;  

                $api_token = $request->header('x-api-key');
                $limit = CommonController::$page_limit;

                $mes = User::checkTokenExpiry($userid, $api_token);
                $status = $mes['status'];   $message = $mes['message'];
                if($status != 1) {
                    return response()->json([ 'status' => $status, 'data' => [], 'message' => $message]);
                }   else {
                    if($userid > 0) { 
                        $type_of_proof =  1;

                        $exuser = ServiceProvider::where('user_id', $userid)->first();
                        $accepted_formats = ['jpeg', 'jpg', 'png'];

                        if(empty($exuser)) {
                            return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid Service Provider']);
                        }

                        $exuser->type_of_proof = $type_of_proof;
                        /*  Aadhaar Front and Back */
                        $image = $request->file('id_proof_front');
                        if (!empty($image) && $image != 'null') {
                            $ext = $image->getClientOriginalExtension();
                            if(!in_array($ext, $accepted_formats)) {
                                return response()->json(['status' => 0, 'message' => 'File Format Wrong.Please upload PNG,JPEG,JPG']);
                            }
              
                            $spdocsImage = 'spdocs-' .rand().time() . '.' . $image->getClientOriginalExtension();

                            $destinationPath = public_path('/uploads/userdocs');

                            $image->move($destinationPath, $spdocsImage);

                            $exuser->id_proof_front =  $spdocsImage;   
                        }

                        $image = $request->file('id_proof_back');
                        if (!empty($image)) {
                            $ext = $image->getClientOriginalExtension();
                            if(!in_array($ext, $accepted_formats)) {
                                return response()->json(['status' => 0, 'message' => 'File Format Wrong.Please upload PNG,JPEG,JPG']);
                            }

                            $spdocsImage = 'spdocs-' .rand().time() . '.' . $image->getClientOriginalExtension();

                            $destinationPath = public_path('/uploads/userdocs');

                            $image->move($destinationPath, $spdocsImage);

                            $exuser->id_proof_back =  $spdocsImage;  
                        }
                        /* Driving License Front and Back */
                        $image = $request->file('driving_license_front');
                        if (!empty($image) && $image != 'null') {
                            $ext = $image->getClientOriginalExtension();
                            if(!in_array($ext, $accepted_formats)) {
                                return response()->json(['status' => 0, 'message' => 'File Format Wrong.Please upload PNG,JPEG,JPG']);
                            }
              
                            $spdocsImage = 'spdocs-' .rand().time() . '.' . $image->getClientOriginalExtension();

                            $destinationPath = public_path('/uploads/userdocs');

                            $image->move($destinationPath, $spdocsImage);

                            $exuser->driving_license_front =  $spdocsImage;   
                        }

                        $image = $request->file('driving_license_back');
                        if (!empty($image)) {
                            $ext = $image->getClientOriginalExtension();
                            if(!in_array($ext, $accepted_formats)) {
                                return response()->json(['status' => 0, 'message' => 'File Format Wrong.Please upload PNG,JPEG,JPG']);
                            }

                            $spdocsImage = 'spdocs-' .rand().time() . '.' . $image->getClientOriginalExtension();

                            $destinationPath = public_path('/uploads/userdocs');

                            $image->move($destinationPath, $spdocsImage);

                            $exuser->driving_license_back =  $spdocsImage;  
                        }

                        /* Noc Front */
                        $image = $request->file('noc_front');
                        if (!empty($image) && $image != 'null') {
                            $ext = $image->getClientOriginalExtension();
                            if(!in_array($ext, $accepted_formats)) {
                                return response()->json(['status' => 0, 'message' => 'File Format Wrong.Please upload PNG,JPEG,JPG']);
                            }
              
                            $spdocsImage = 'spdocs-' .rand().time() . '.' . $image->getClientOriginalExtension();

                            $destinationPath = public_path('/uploads/userdocs');

                            $image->move($destinationPath, $spdocsImage);

                            $exuser->noc_front =  $spdocsImage;   
                        }
                        $exuser->save();

                        $user = User::where('id', $userid)->first();

                        if($user->is_register_complete < 2) {
                            $user->is_register_complete = 2;
                            $user->step = 2;
                        }
                        
                        $user->save();

                        $user = CommonController::getUserDetails($userid);
 
                        if (!empty($user)) {

                            return response()->json(['status' => 1, 'message' => 'Step 2 Details updated successfully', 'data' => $user]);

                        } else {

                            return response()->json(['status' => 0, 'data' => [], 'message' => 'Updation Failed']);
                        }

                    }   else {
                        return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid Inputs']);
                    }
                }
            }    else {
                return response()->json([ 'status' => 0, 'data' => [], 'message' => $error]);
            }
        }   catch(\Throwable $th) {
            return response()->json(['status' => 0, 'data' => [], 'message' => $th->getMessage()]);
        }  
              
    }

    /* Post Servicer Update the Current Address details */
    public function postServicerUpdateStep3(Request $request) {

        try {   
            $inputJSON = file_get_contents('php://input');

            $input = json_decode($inputJSON, TRUE);

            $requiredParams = ['user_id', 'api_token', 'flat', 'landmark', 'address'];

            $error = $this->checkParams($input, $requiredParams, $request);

            if(empty($error)) {

                $userid = ((isset($input) && isset($input['user_id']))) ? $input['user_id'] : 0;  

                $api_token = $request->header('x-api-key');
                $limit = CommonController::$page_limit;

                $mes = User::checkTokenExpiry($userid, $api_token);
                $status = $mes['status'];   $message = $mes['message'];
                if($status != 1) {
                    return response()->json([ 'status' => $status, 'data' => [], 'message' => $message]);
                }   else {
                    if($userid > 0) { 
                        $current_house = ((isset($input) && isset($input['flat']))) ? $input['flat'] : ''; 
                        $current_landmark = ((isset($input) && isset($input['landmark']))) ? $input['landmark'] : ''; 
                        $current_address = ((isset($input) && isset($input['address']))) ? $input['address'] : ''; 

                        $exuser = ServiceProvider::where('user_id', $userid)->first(); 

                        if(empty($exuser)) {
                            return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid Service Provider']);
                        }

                        $exuser->current_house = $current_house;
                        $exuser->current_landmark = $current_landmark;
                        $exuser->current_address = $current_address;
 
                        $exuser->save();

                        $user = User::where('id', $userid)->first();
                        if($user->is_register_complete < 3) {
                            $user->is_register_complete = 3;
                            $user->step = 3;
                            $user->save();
                        }                        

                        $user = CommonController::getUserDetails($userid);
 
                        if (!empty($user)) {

                            return response()->json(['status' => 1, 'message' => 'Step 3 Details updated successfully', 'data' => $user]);

                        } else {

                            return response()->json(['status' => 0, 'data' => [], 'message' => 'Updation Failed']);
                        }

                    }   else {
                        return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid Inputs']);
                    }
                }
            }    else {
                return response()->json([ 'status' => 0, 'data' => [], 'message' => $error]);
            }
        }   catch(\Throwable $th) {
            return response()->json(['status' => 0, 'data' => [], 'message' => $th->getMessage()]);
        }  
              
    }

    /* Post Servicer Update the Category, Sub Category and Service details */
    public function postServicerUpdateStep4_new() {

        try {   
            $inputJSON = file_get_contents('php://input');

            $input = json_decode($inputJSON, TRUE);

            $requiredParams = ['user_id', 'api_token', 'sub_category_ids', 'service_ids'];

            $error = $this->checkParams($input, $requiredParams, $request);

            if(empty($error)) {

                $userid = ((isset($input) && isset($input['user_id']))) ? $input['user_id'] : 0;  

                $api_token = $request->header('x-api-key');
                $limit = CommonController::$page_limit;

                $mes = User::checkTokenExpiry($userid, $api_token);
                $status = $mes['status'];   $message = $mes['message'];
                if($status != 1) {
                    return response()->json([ 'status' => $status, 'data' => [], 'message' => $message]);
                }   else {
                    if($userid > 0) { 
                        $sub_category_ids = ((isset($input) && isset($input['sub_category_ids']))) ? $input['sub_category_ids'] : "";   
                        $service_ids = ((isset($input) && isset($input['service_ids']))) ? $input['service_ids'] : "";   

                        $exuser = ServiceProvider::where('user_id', $userid)->first(); 

                        if(empty($exuser)) {
                            return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid Service Provider']);
                        }

                        $sub_category_ids = str_replace('[', '', $sub_category_ids);
                        $sub_category_ids = str_replace(']', '', $sub_category_ids);

                        $service_ids = str_replace('[', '', $service_ids);
                        $service_ids = str_replace(']', '', $service_ids);

                        $category_id = 0;
                        $subcat_arr = explode(',', $sub_category_ids);
                        if(is_array($subcat_arr)) {
                            $category_id = SubCategory::where('id', $subcat_arr[0])->value('category_id');
                        }                        

                        $user = User::where('id', $userid)->first();
                        if($user->is_register_complete < 4) {
                            $user->is_register_complete = 4;
                            $user->step = 4;
                            $user->save();
                        }                    

                        ServiceProvider::where('user_id', $userid)->update([
                            'category_id' => $category_id,
                            'sub_category_ids' => $sub_category_ids,
                            'service_ids' => $service_ids,
                        ]);

                        $user = CommonController::getUserDetails($userid);
 
                        if (!empty($user)) {

                            return response()->json(['status' => 1, 'message' => 'Step 4 Details updated successfully', 'data' => $user]);

                        } else {

                            return response()->json(['status' => 0, 'data' => [], 'message' => 'Updation Failed']);
                        }

                    }   else {
                        return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid Inputs']);
                    }
                }
            }    else {
                return response()->json([ 'status' => 0, 'data' => [], 'message' => $error]);
            }
        }   catch(\Throwable $th) {
            return response()->json(['status' => 0, 'data' => [], 'message' => $th->getMessage()]);
        }  
    }

    /* Post Servicer Update the Category, Sub Category and Service details */
    public function postServicerUpdateStep4(Request $request) {

        try {   
            $inputJSON = file_get_contents('php://input');

            $input = json_decode($inputJSON, TRUE);

            $requiredParams = ['user_id', 'api_token', 'subcatservices'];

            $error = $this->checkParams($input, $requiredParams, $request);

            if(empty($error)) {

                $userid = ((isset($input) && isset($input['user_id']))) ? $input['user_id'] : 0;  

                $api_token = $request->header('x-api-key');
                $limit = CommonController::$page_limit;

                $mes = User::checkTokenExpiry($userid, $api_token);
                $status = $mes['status'];   $message = $mes['message'];
                if($status != 1) {
                    return response()->json([ 'status' => $status, 'data' => [], 'message' => $message]);
                }   else {
                    if($userid > 0) { 
                        $subcatservices = ((isset($input) && isset($input['subcatservices']))) ? $input['subcatservices'] : [];   

                        $exuser = ServiceProvider::where('user_id', $userid)->first(); 

                        if(empty($exuser)) {
                            return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid Service Provider']);
                        }
                        if(!is_array($subcatservices)) {
                            return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid subcatservices Input']);
                        }

                        $subcats = []; $services = []; $category_id = '';
                        if(is_array($subcatservices) && count($subcatservices)>0) {
                            foreach ($subcatservices as $key => $value) {
                                $subcats[] = $value['sub_cat_id'];
                                $service_array = $value['service_ids'];
                                if(!is_array($service_array)) {
                                    return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid Services Input']);
                                }else{
                                    $services = array_merge($services, $service_array);
                                    array_unique($services);
                                }
                            }
                            array_unique($subcats);
                            $category_id = SubCategory::where('id', $subcats[0])->value('category_id');
                            $exuser->category_id = $category_id;
                            $exuser->sub_category_ids = implode(',', $subcats);
                            $exuser->service_ids = implode(',', $services); 

                            $exuser->save();
                        }                        

                        $user = User::where('id', $userid)->first();
                        if($user->is_register_complete < 4) {
                            $user->is_register_complete = 4;
                            $user->step = 4;
                            $user->save();
                        }                        

                        $user = CommonController::getUserDetails($userid);
 
                        if (!empty($user)) {

                            return response()->json(['status' => 1, 'message' => 'Step 4 Details updated successfully', 'data' => $user]);

                        } else {

                            return response()->json(['status' => 0, 'data' => [], 'message' => 'Updation Failed']);
                        }

                    }   else {
                        return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid Inputs']);
                    }
                }
            }    else {
                return response()->json([ 'status' => 0, 'data' => [], 'message' => $error]);
            }
        }   catch(\Throwable $th) {
            return response()->json(['status' => 0, 'data' => [], 'message' => $th->getMessage()]);
        }  
              
    }

    /* Post Servicer Update the Location of Zones where he can work */
    public function postServicerUploadServiceZone(Request $request) {

        try {   
            $inputJSON = file_get_contents('php://input');

            $input = json_decode($inputJSON, TRUE);

            $requiredParams = ['user_id', 'api_token', 'zone_id'];

            $error = $this->checkParams($input, $requiredParams, $request);

            if(empty($error)) {

                $userid = ((isset($input) && isset($input['user_id']))) ? $input['user_id'] : 0;  

                $api_token = $request->header('x-api-key');
                $limit = CommonController::$page_limit;

                $mes = User::checkTokenExpiry($userid, $api_token);
                $status = $mes['status'];   $message = $mes['message'];
                if($status != 1) {
                    return response()->json([ 'status' => $status, 'data' => [], 'message' => $message]);
                }   else {
                    if($userid > 0) { 
                        $zone_id = ((isset($input) && isset($input['zone_id']))) ? $input['zone_id'] : [];   

                        $exuser = ServiceProvider::where('user_id', $userid)->first(); 

                        if(empty($exuser)) {
                            return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid Service Provider']);
                        }
                        if(!is_array($zone_id)) {
                            return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid Zone Input']);
                        }
 
                        if(is_array($zone_id) && count($zone_id)>0) {
                            array_unique($zone_id);
                            foreach ($zone_id as $key => $value) {
                                $zone = Zones::find($value);
                                if(empty($zone)) {
                                    return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid Zone '.$value]);
                                }
                            }
                             
                            $exuser->zone_ids = implode(',', $zone_id);

                            $exuser->save();
                        }                        

                        $user = User::where('id', $userid)->first();
                        if($user->is_register_complete < 5) {
                            $user->is_register_complete = 5;
                            $user->step = 5;
                            $user->save();
                        }                        

                        $user = CommonController::getUserDetails($userid);
 
                        if (!empty($user)) {

                            return response()->json(['status' => 1, 'message' => 'Step 5 Details updated successfully', 'data' => $user]);

                        } else {

                            return response()->json(['status' => 0, 'data' => [], 'message' => 'Updation Failed']);
                        }

                    }   else {
                        return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid Inputs']);
                    }
                }
            }    else {
                return response()->json([ 'status' => 0, 'data' => [], 'message' => $error]);
            }
        }   catch(\Throwable $th) {
            return response()->json(['status' => 0, 'data' => [], 'message' => $th->getMessage()]);
        }  
              
    }

    /* Post Servicer Update the Other service needed - 2nd level */
    public function postServicerUploadOtherService(Request $request) {

        try {   
            $inputJSON = file_get_contents('php://input');

            $input = json_decode($inputJSON, TRUE);

            $requiredParams = ['user_id', 'api_token', 'service_name', 'service_description'];

            $error = $this->checkParams($input, $requiredParams, $request);

            if(empty($error)) {

                $userid = ((isset($input) && isset($input['user_id']))) ? $input['user_id'] : 0;  

                $api_token = $request->header('x-api-key');
                $limit = CommonController::$page_limit;

                $mes = User::checkTokenExpiry($userid, $api_token);
                $status = $mes['status'];   $message = $mes['message'];
                if($status != 1) {
                    return response()->json([ 'status' => $status, 'data' => [], 'message' => $message]);
                }   else {
                    if($userid > 0) { 
                        $service_name = ((isset($input) && isset($input['service_name']))) ? $input['service_name'] : '';   
                        $service_description = ((isset($input) && isset($input['service_description']))) ? $input['service_description'] : '';   

                        $exuser = ServiceProvider::where('user_id', $userid)->first(); 

                        if(empty($exuser)) {
                            return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid Service Provider']);
                        } 

                        DB::table('em_servicer_otherservices')->insert([
                            'service_provider_id' => $userid,
                            'service_name' => $service_name,
                            'service_description' => $service_description,
                            'status' => 'PENDING',
                            'created_at' => date('Y-m-d H:i:s'),
                        ]);              

                        return response()->json(['status' => 1, 'message' => 'Service needed Details updated successfully', 'data' => []]);


                    }   else {
                        return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid Inputs']);
                    }
                }
            }    else {
                return response()->json([ 'status' => 0, 'data' => [], 'message' => $error]);
            }
        }   catch(\Throwable $th) {
            return response()->json(['status' => 0, 'data' => [], 'message' => $th->getMessage()]);
        }  
              
    }

     /* Post Servicer Agree the Terms and Conditions */
    public function postServicerUploadStep5(Request $request) {

        try {   
            $inputJSON = file_get_contents('php://input');

            $input = json_decode($inputJSON, TRUE);

            $requiredParams = ['user_id', 'api_token'];

            $error = $this->checkParams($input, $requiredParams, $request);

            if(empty($error)) {

                $userid = ((isset($input) && isset($input['user_id']))) ? $input['user_id'] : 0;  

                $api_token = $request->header('x-api-key');
                $limit = CommonController::$page_limit;

                $mes = User::checkTokenExpiry($userid, $api_token);
                $status = $mes['status'];   $message = $mes['message'];
                if($status != 1) {
                    return response()->json([ 'status' => $status, 'data' => [], 'message' => $message]);
                }   else {
                    if($userid > 0) { 
                        $zone_id = ((isset($input) && isset($input['zone_id']))) ? $input['zone_id'] : [];   

                        $exuser = ServiceProvider::where('user_id', $userid)->first(); 

                        if(empty($exuser)) {
                            return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid Service Provider']);
                        }                        

                        $user = User::where('id', $userid)->first();
                        if($user->is_register_complete < 6) {
                            $user->is_register_complete = 6;
                            $user->step = 6;
                            $user->save();
                        }                        

                        $user = CommonController::getUserDetails($userid);
 
                        if (!empty($user)) {

                            return response()->json(['status' => 1, 'message' => 'Step 6 Details updated successfully', 'data' => $user]);

                        } else {

                            return response()->json(['status' => 0, 'data' => [], 'message' => 'Updation Failed']);
                        }

                    }   else {
                        return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid Inputs']);
                    }
                }
            }    else {
                return response()->json([ 'status' => 0, 'data' => [], 'message' => $error]);
            }
        }   catch(\Throwable $th) {
            return response()->json(['status' => 0, 'data' => [], 'message' => $th->getMessage()]);
        }  
              
    }

    /* Post Servicer Update PAN Details */
    public function postServicerUpdatePAN(Request $request) {

        try {   
            $input = $request->all();

            $requiredParams = ['user_id', 'api_token', 'pan_name', 'pan_number'];

            $error = $this->checkParams($input, $requiredParams, $request, true);

            if(empty($error)) {

                $userid = ((isset($input) && isset($input['user_id']))) ? $input['user_id'] : 0;  

                $api_token = $request->header('x-api-key');
                $limit = CommonController::$page_limit;

                $mes = User::checkTokenExpiry($userid, $api_token);
                $status = $mes['status'];   $message = $mes['message'];
                if($status != 1) {
                    return response()->json([ 'status' => $status, 'data' => [], 'message' => $message]);
                }   else {
                    if($userid > 0) { 
                        $pan_name = ((isset($input) && isset($input['pan_name']))) ? $input['pan_name'] : '';   
                        $pan_number = ((isset($input) && isset($input['pan_number']))) ? $input['pan_number'] : '';   

                        if(empty(trim($pan_name)) || empty(trim($pan_number))) {
                            return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid PAN Details']);
                        }
                        $exuser = ServiceProvider::where('user_id', $userid)->first(); 

                        if(empty($exuser)) {
                            return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid Service Provider']);
                        }                        

                        $user = ServiceProvider::where('user_id', $userid)->first();
                         
                        $user->pan_name = $pan_name;
                        $user->pan_number = $pan_number;

                        $accepted_formats = ['jpeg', 'jpg', 'png'];
                        $image = $request->file('pan_front');
                        if (!empty($image)) {
                            $ext = $image->getClientOriginalExtension();
                            if(!in_array($ext, $accepted_formats)) {
                                return response()->json(['status' => 0, 'message' => 'File Format Wrong.Please upload PNG,JPEG,JPG']);
                            }

                            $spdocsImage = 'spdocs-' .rand().time() . '.' . $image->getClientOriginalExtension();

                            $destinationPath = public_path('/uploads/userdocs');

                            $image->move($destinationPath, $spdocsImage);

                            $user->pan_card_front =  $spdocsImage;  
                        }

                        $image = $request->file('pan_back');
                        if (!empty($image)) {
                            $ext = $image->getClientOriginalExtension();
                            if(!in_array($ext, $accepted_formats)) {
                                return response()->json(['status' => 0, 'message' => 'File Format Wrong.Please upload PNG,JPEG,JPG']);
                            }

                            $spdocsImage = 'spdocs-' .rand().time() . '.' . $image->getClientOriginalExtension();

                            $destinationPath = public_path('/uploads/userdocs');

                            $image->move($destinationPath, $spdocsImage);

                            $user->pan_card_back =  $spdocsImage;  
                        }
                        $user->save();                                           

                        $user = CommonController::getUserDetails($userid);
 
                        if (!empty($user)) {

                            return response()->json(['status' => 1, 'message' => 'PAN Details updated successfully', 'data' => $user]);

                        } else {

                            return response()->json(['status' => 0, 'data' => [], 'message' => 'Updation Failed']);
                        }

                    }   else {
                        return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid Inputs']);
                    }
                }
            }    else {
                return response()->json([ 'status' => 0, 'data' => [], 'message' => $error]);
            }
        }   catch(\Throwable $th) {
            return response()->json(['status' => 0, 'data' => [], 'message' => $th->getMessage()]);
        }  
              
    }

    /* Post Servicer Update Bank Details */
    public function postServicerUpdateBank(Request $request) {

        try {   
            $input = $request->all();

            $requiredParams = ['user_id', 'api_token', 'bank_id', 'acc_name', 'acc_no', 'ifsc_code', 'cheque_img'];

            $error = $this->checkParams($input, $requiredParams, $request, true);

            if(empty($error)) {

                $userid = ((isset($input) && isset($input['user_id']))) ? $input['user_id'] : 0;  

                $api_token = $request->header('x-api-key');
                $limit = CommonController::$page_limit;

                $mes = User::checkTokenExpiry($userid, $api_token);
                $status = $mes['status'];   $message = $mes['message'];
                if($status != 1) {
                    return response()->json([ 'status' => $status, 'data' => [], 'message' => $message]);
                }   else {
                    if($userid > 0) { 
                        $bank_id = ((isset($input) && isset($input['bank_id']))) ? $input['bank_id'] : 0;   
                        $acc_name = ((isset($input) && isset($input['acc_name']))) ? $input['acc_name'] : '';
                        $acc_no = ((isset($input) && isset($input['acc_no']))) ? $input['acc_no'] : '';
                        $ifsc_code = ((isset($input) && isset($input['ifsc_code']))) ? $input['ifsc_code'] : '';   

                        if(empty(trim($acc_name)) || empty(trim($acc_no)) || empty(trim($ifsc_code))) {
                            return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid Bank Details']);
                        }

                        $banks = Banks::where('status', 'ACTIVE')->where('id', $bank_id)->get();
                        
                        if($banks->isNotEmpty()) {   }   else {
                            return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid Bank']);
                        } 
                        $user = ServiceProvider::where('user_id', $userid)->first(); 

                        if(empty($user)) {
                            return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid Service Provider']);
                        }    
                         
                        $user->bank_id = $bank_id;
                        $user->account_name = $acc_name;
                        $user->account_number = $acc_no;
                        $user->ifsc_code = $ifsc_code;

                        $accepted_formats = ['jpeg', 'jpg', 'png'];
                        $image = $request->file('cheque_img');
                        if (!empty($image)) {
                            $ext = $image->getClientOriginalExtension();
                            if(!in_array($ext, $accepted_formats)) {
                                return response()->json(['status' => 0, 'message' => 'File Format Wrong.Please upload PNG,JPEG,JPG']);
                            }

                            $spdocsImage = 'spdocs-' .rand().time() . '.' . $image->getClientOriginalExtension();

                            $destinationPath = public_path('/uploads/userdocs');

                            $image->move($destinationPath, $spdocsImage);

                            $user->cheque_image =  $spdocsImage;  
                        } 

                        $user->save();                                           

                        $user = CommonController::getUserDetails($userid);
 
                        if (!empty($user)) {

                            return response()->json(['status' => 1, 'message' => 'Bank Details updated successfully', 'data' => $user]);

                        } else {

                            return response()->json(['status' => 0, 'data' => [], 'message' => 'Updation Failed']);
                        }

                    }   else {
                        return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid Inputs']);
                    }
                }
            }    else {
                return response()->json([ 'status' => 0, 'data' => [], 'message' => $error]);
            }
        }   catch(\Throwable $th) {
            return response()->json(['status' => 0, 'data' => [], 'message' => $th->getMessage()]);
        }  
              
    }

    /* Update Servicers Profile Image */
    public function postServicerUpdateProfileImage(Request $request)   {

        try {   
            $input = $request->all();

            $requiredParams = ['user_id', 'api_token'];

            $error = $this->checkParams($input, $requiredParams, $request, true);

            if(empty($error)) {
                $userid = $input['user_id'];
                $api_token = $request->header('x-api-key');
                $mes = User::checkTokenExpiry($userid, $api_token);
                $status = $mes['status'];   $message = $mes['message'];
                if($status != 1) {
                    return response()->json([ 'status' => $status, 'data' => [], 'message' => $message]);
                }   else {
                    $user = User::find($userid);
                    /*  Profile image of the User */
                    $accepted_formats = ['jpeg', 'jpg', 'png'];
                    $image = $request->file('profile_image');
                    if (!empty($image) && $image != 'null') {
                        $ext = $image->getClientOriginalExtension();
                        if(!in_array($ext, $accepted_formats)) {
                            return response()->json(['status' => 0, 'message' => 'File Format Wrong.Please upload PNG,JPEG,JPG']);
                        }
          
                        $spdocsImage = 'spdocs-' .rand().time() . '.' . $image->getClientOriginalExtension();

                        $destinationPath = public_path('/uploads/userdocs');

                        $image->move($destinationPath, $spdocsImage);

                        $user->profile_image =  $spdocsImage;   

                        $user->save();
                    }

                    $user = CommonController::getUserDetails($userid);
                    if(!empty($user)) {
                        return response()->json(['status' => 1, 'message' => 'Service Provider Details', 'data' => $user]);
                    }   else {
                        return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid User']);
                    }
                    
                }
            }    else {
                return response()->json([ 'status' => 0, 'data' => [], 'message' => $error]);
            }
        }   catch(\Throwable $th) {
            return response()->json(['status' => 0, 'data' => [], 'message' => $th->getMessage()]);
        }  
    }

    /* Post Servicer Update Emergency Contact Details */
    public function postServicerUpdateEmergencyContact(Request $request) {

        try {   
            $inputJSON = file_get_contents('php://input');

            $input = json_decode($inputJSON, TRUE);

            $requiredParams = ['user_id', 'api_token', 'contact_name', 'contact_number', 'contact_relationship'];

            $error = $this->checkParams($input, $requiredParams, $request);

            if(empty($error)) {

                $userid = ((isset($input) && isset($input['user_id']))) ? $input['user_id'] : 0;  

                $api_token = $request->header('x-api-key');
                $limit = CommonController::$page_limit;

                $mes = User::checkTokenExpiry($userid, $api_token);
                $status = $mes['status'];   $message = $mes['message'];
                if($status != 1) {
                    return response()->json([ 'status' => $status, 'data' => [], 'message' => $message]);
                }   else {
                    if($userid > 0) { 
                        $emergency_contact_name = ((isset($input) && isset($input['contact_name']))) ? $input['contact_name'] : '';   
                        $emergency_contact_number = ((isset($input) && isset($input['contact_number']))) ? $input['contact_number'] : '';
                        $emergency_contact_relationship = ((isset($input) && isset($input['contact_relationship']))) ? $input['contact_relationship'] : '';    

                        if(empty(trim($emergency_contact_name)) || empty(trim($emergency_contact_number)) || empty(trim($emergency_contact_relationship))) {
                            return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid Emergency Contact Details']);
                        }

                        $user = ServiceProvider::where('user_id', $userid)->first(); 

                        if(empty($user)) {
                            return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid Service Provider']);
                        }    
                         
                        $user->emergency_contact_name = $emergency_contact_name;
                        $user->emergency_contact_number = $emergency_contact_number;
                        $user->emergency_contact_relationship = $emergency_contact_relationship;

                        $user->save();                                           

                        $user = CommonController::getUserDetails($userid);
 
                        if (!empty($user)) {

                            return response()->json(['status' => 1, 'message' => 'Emergency Contact Details updated successfully', 'data' => $user]);

                        } else {

                            return response()->json(['status' => 0, 'data' => [], 'message' => 'Updation Failed']);
                        }

                    }   else {
                        return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid Inputs']);
                    }
                }
            }    else {
                return response()->json([ 'status' => 0, 'data' => [], 'message' => $error]);
            }
        }   catch(\Throwable $th) {
            return response()->json(['status' => 0, 'data' => [], 'message' => $th->getMessage()]);
        }  
              
    }

    /* Post Servicer Update GST Details */
    public function postServicerUpdateGST(Request $request) {

        try {   
            $inputJSON = file_get_contents('php://input');

            $input = json_decode($inputJSON, TRUE);

            $requiredParams = ['user_id', 'api_token', 'gst_name', 'gst_number'];

            $error = $this->checkParams($input, $requiredParams, $request);

            if(empty($error)) {

                $userid = ((isset($input) && isset($input['user_id']))) ? $input['user_id'] : 0;  

                $api_token = $request->header('x-api-key');
                $limit = CommonController::$page_limit;

                $mes = User::checkTokenExpiry($userid, $api_token);
                $status = $mes['status'];   $message = $mes['message'];
                if($status != 1) {
                    return response()->json([ 'status' => $status, 'data' => [], 'message' => $message]);
                }   else {
                    if($userid > 0) { 
                        $gst_name = ((isset($input) && isset($input['gst_name']))) ? $input['gst_name'] : '';   
                        $gst_number = ((isset($input) && isset($input['gst_number']))) ? $input['gst_number'] : '';   

                        if(empty(trim($gst_name)) || empty(trim($gst_number))) {
                            return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid GST Details']);
                        }
                        $exuser = ServiceProvider::where('user_id', $userid)->first(); 

                        if(empty($exuser)) {
                            return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid Service Provider']);
                        }                        

                        $user = ServiceProvider::where('user_id', $userid)->first();
                         
                        $user->gst_name = $gst_name;
                        $user->gst_number = $gst_number;
                        $user->save();                                          

                        $user = CommonController::getUserDetails($userid);
 
                        if (!empty($user)) {

                            return response()->json(['status' => 1, 'message' => 'GST Details updated successfully', 'data' => $user]);

                        } else {

                            return response()->json(['status' => 0, 'data' => [], 'message' => 'Updation Failed']);
                        }

                    }   else {
                        return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid Inputs']);
                    }
                }
            }    else {
                return response()->json([ 'status' => 0, 'data' => [], 'message' => $error]);
            }
        }   catch(\Throwable $th) {
            return response()->json(['status' => 0, 'data' => [], 'message' => $th->getMessage()]);
        }  
              
    }

    /* Post Servicer Update Leave Details */
    public function postServicerUpdateLeave(Request $request) {

        try {   
            $inputJSON = file_get_contents('php://input');

            $input = json_decode($inputJSON, TRUE);

            //$requiredParams = ['user_id', 'api_token', 'yes', 'no'];

            $requiredParams = ['user_id', 'api_token', 'leave_date', 'leave_slots'];

            $error = $this->checkParams($input, $requiredParams, $request);

            if(empty($error)) {

                $userid = ((isset($input) && isset($input['user_id']))) ? $input['user_id'] : 0;  
                $leave_date = ((isset($input) && isset($input['leave_date']))) ? $input['leave_date'] : "";  
                $leave_slots = ((isset($input) && isset($input['leave_slots']))) ? $input['leave_slots'] : [];  

                if(empty($leave_date)) {
                    return response()->json(['status' => 0, 'message' => 'Please input Leave Date']);
                }

                if(!is_array($leave_slots)) {
                    return response()->json(['status' => 0, 'message' => 'Please input Leave slots in Array']);
                }

                $api_token = $request->header('x-api-key');
                $limit = CommonController::$page_limit;

                $mes = User::checkTokenExpiry($userid, $api_token);
                $status = $mes['status'];   $message = $mes['message'];
                if($status != 1) {
                    return response()->json([ 'status' => $status, 'data' => [], 'message' => $message]);
                }   else {
                    if($userid > 0) { 
                        $exuser = ServiceProvider::where('user_id', $userid)->first(); 

                        if(empty($exuser)) {
                            return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid Service Provider']);
                        }    

                        $leave_slots = implode(',', $leave_slots);
                        $leave_date = date('Y-m-d', strtotime($leave_date));

                        if(!empty($exuser)) {
                            $service_provider_id = $userid;
                            $servicer = DB::table('em_servicer_leave_slots')
                                ->where('servicer_id', $service_provider_id)
                                ->where('leave_date', $leave_date)
                                ->first();
                            if(!empty($servicer)) {
                                DB::table('em_servicer_leave_slots')
                                    ->where('servicer_id', $service_provider_id)
                                    ->where('leave_date', $leave_date)
                                    ->update(['leave_date' => $leave_date, 'leave_slots' => $leave_slots, 
                                        'updated_at'=>date('Y-m-d H:i:s')]);
                            }   else {
                                DB::table('em_servicer_leave_slots')
                                    ->insert(['servicer_id' => $service_provider_id, 
                                        'leave_date' => $leave_date, 'leave_slots' => $leave_slots, 
                                        'created_at'=>date('Y-m-d H:i:s')]);
                            }
                            DB::table('em_servicer_leave_slots')
                                    ->where('servicer_id', $service_provider_id)
                                    ->where('leave_slots', '')->delete();

                            $servicer = DB::table('em_servicer_leave_slots')->where('servicer_id', $service_provider_id)->get();
                            return response()->json(['status' => 1, 'message' => 'Servicer Provider Leave Details Updated', 
                                'details' => $servicer]);
                        }


                        /*$today_date =  date('Y-m-d');

                        $datetime = new DateTime(date('Y-m-d'));
                        $datetime->modify('+1 day');
                        $tomorrow_date =  $datetime->format('Y-m-d');

                        $datetime = new DateTime(date('Y-m-d'));
                        $datetime->modify('+2 day');
                        $dayaftertomorrow_date =  $datetime->format('Y-m-d');

                        if (($date != $today_date)) {
                            if ($date != $tomorrow_date) {
                                if ($date != $dayaftertomorrow_date) {
                                    return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid date; Only current 3 dates are accepted']); 
                                }
                            }
                        }                    

                        $user = ServiceProvider::where('user_id', $userid)->first();
                        if(!empty($user)) {
                            $yes_input = trim($yes);
                            if(!empty($yes_input)){
                                $yes_arr = explode(",", $yes_input);
                            }else{
                                $yes_arr = [];
                            }

                            if(count($yes_arr) != count(array_unique($yes_arr))){
                                return response()->json([ 'status' => 0, 'data' => [], 'message' => 'yes - has duplicate values']); 
                            } 
                            
                            $no_input = trim($no);
                            if(!empty($no_input)){
                                $no_arr = explode(",", $no_input);
                            }else{
                                $no_arr = [];
                            }

                            if (count($no_arr) != count(array_unique($no_arr))) {
                                return response()->json([ 'status' => 0, 'data' => [], 'message' => 'no - has duplicate values']);  
                            }

                            foreach ($yes_arr as $num) {
                                if (in_array($num, $no_arr)) {
                                    return response()->json([ 'status' => 0, 'data' => [], 'message' => 'yes and no - has duplicate values']);   
                                }
                            }

                            foreach ($no_arr as $num) {
                                if (in_array($num, $yes_arr)) {
                                    return response()->json([ 'status' => 0, 'data' => [], 'message' => 'yes and no - has duplicate values']);   
                                }
                            }

                            $total_slot_count = count($yes_arr) + count($no_arr);

                            $actual_count = DB::table('em_slots')->where('status', 'ACTIVE')->count('id');  

                            $getzone =  $exuser->zone_ids; 

                            if (empty(trim($getzone))) {
                                return response()->json([ 'status' => 0, 'data' => [], 'message' => 'This employee has no zone_id selected; So could not update slot details']);  
                            }

                            $zonearr = explode(',', $getzone);
                            $i=0;
                            $params = '';
                            
                            if(count($yes_arr) >0){
                            while($i < count($yes_arr)){
                                foreach($zonearr as $zone) {
                                    $params .= "(".$userid.",".$zone.",'".$date."',".$yes_arr[$i].",'YES'),";
                                }
                                
                                $i++;
                                }
                            }

                            if(count($no_arr) >0){
                                $i = 0;
                                while ($i < count($no_arr)) {
                                    foreach($zonearr as $zone) {
                                        $params .= "(" . $userid . "," . $zone . ",'" . $date . "'," . $no_arr[$i] . ",'NO'),";
                                    }
                                    $i++;
                                }
                            }

                            $params = rtrim($params, ',');

                            DB::table('em_available_slot')->where('emp_id', $userid)->where('date', $date)->delete();
                            
                            $slotchecks = DB::select("SELECT * FROM em_available_slot JOIN em_booking ON em_booking.job_slot = em_available_slot.slot_id AND em_booking.job_date = em_available_slot.date WHERE em_booking.service_provider_id = $userid   AND (status != 'COMPLETED' OR status != 'CANCELLED')");

                            if (!empty($slotchecks) && count($slotchecks)>0) {
                                return response()->json(['status' => 0, 'data' => [], 'message' => 'Already U have booking in that slot']);
                            }

                            DB::select("INSERT INTO em_available_slot(`emp_id`, `zone_id`, `date`, `slot_id`, `available`) VALUES $params");
 
                            return response()->json(['status' => 1, 'data' => [], 'message' => 'Available slots updated for employee ID: '.$userid.' for date : '.$date]);

                        }   else {
                            return response()->json(['status' => 0, 'data' => [], 'message' => 'Invalid Service Provider']);
                        } */

                    }   else {
                        return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid Inputs']);
                    }
                }
            }    else {
                return response()->json([ 'status' => 0, 'data' => [], 'message' => $error]);
            }
        }   catch(\Throwable $th) {
            return response()->json(['status' => 0, 'data' => [], 'message' => $th->getMessage()]);
        }  
              
    }

    /* Post Servicer View Profile Details */
    public function postServicerViewProfile(Request $request) {

        try {   
            $inputJSON = file_get_contents('php://input');

            $input = json_decode($inputJSON, TRUE); 

            $requiredParams = ['user_id', 'api_token'];

            $error = $this->checkParams($input, $requiredParams, $request);

            if(empty($error)) {

                $userid = ((isset($input) && isset($input['user_id']))) ? $input['user_id'] : 0;  

                $api_token = $request->header('x-api-key');

                $mes = User::checkTokenExpiry($userid, $api_token);
                $status = $mes['status'];   $message = $mes['message'];
                if($status != 1) {
                    return response()->json([ 'status' => $status, 'data' => [], 'message' => $message]);
                }   else {
                    if($userid > 0) { 
                        $exuser = CommonController::getUserDetails($userid);

                        if(!empty($exuser)) {
                            return response()->json([ 'status' => 1, 'data' => $exuser, 'message' => 'Service Provider Details']);
                        }   else {
                            return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid Service Provider']);
                        } 

                    }   else {
                        return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid Inputs']);
                    }
                }
            }    else {
                return response()->json([ 'status' => 0, 'data' => [], 'message' => $error]);
            }
        }   catch(\Throwable $th) {
            return response()->json(['status' => 0, 'data' => [], 'message' => $th->getMessage()]);
        }  
              
    }

    /* Get Servicer Available Slot Details */
    public function postServicerAvailableSlots(Request $request) {

        try {   
            $inputJSON = file_get_contents('php://input');

            $input = json_decode($inputJSON, TRUE);

            $requiredParams = ['user_id', 'api_token', 'date'];

            $error = $this->checkParams($input, $requiredParams, $request);

            if(empty($error)) {

                $userid = ((isset($input) && isset($input['user_id']))) ? $input['user_id'] : 0;  
                $date = $input['date']; 
                $current_date = date('Y-m-d');

                if(strtotime($date) < strtotime($current_date)) {
                    return response()->json(['status' => 0, 'data' => [], 'message' => 'Previous Dates are Not Allowed']);
                }

                $api_token = $request->header('x-api-key'); 

                $mes = User::checkTokenExpiry($userid, $api_token);
                $status = $mes['status'];   $message = $mes['message'];
                if($status != 1) {
                    return response()->json([ 'status' => $status, 'data' => [], 'message' => $message]);
                }   else {
                    if($userid > 0) { 
                        $exuser = ServiceProvider::where('user_id', $userid)->first(); 

                        if(empty($exuser)) {
                            return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid Service Provider']);
                        }     
                        
                        /*$select_slots = DB::table('em_available_slot')->where('emp_id', $userid)
                            ->where('date', $date)
                            ->groupby('slot_id')
                            ->get();*/
                        $current_time = date('H:i'); 

                        Slots::$service_provider_id = $userid;
                        $slots = Slots::where('status', 'ACTIVE');

                        if(strtotime($date) == strtotime($current_date)) {
                            $slots->where('from_time', '>=', $current_time);
                        }
                            //->where('to_time', '<=', $current_time)
                        $slots = $slots->orderby('position', 'ASC')->get();

                        $leavedays = [];

                        $leave_date = date('Y-m-d', strtotime($date));

                        $servicer = DB::select('SELECT  em_servicer_leave_slots.*,
                                    GROUP_CONCAT(em_slots.slot_name ORDER BY em_slots.id) slotname
                            FROM    em_servicer_leave_slots 
                                    INNER JOIN em_slots
                                        ON FIND_IN_SET(em_slots.id, em_servicer_leave_slots.leave_slots) > 0
                                    WHERE leave_date = "'.$leave_date.'" 
                                    GROUP BY em_servicer_leave_slots.id');
                       
                        if(!empty($servicer)) {
                            foreach ($servicer as $key => $value) {
                                $leavedays_str = $value->leave_slots;
                                $leavedays = explode(',', $leavedays_str);
                            }
                        }
                        
                        if($slots->isNotEmpty()) {

                            foreach ($slots as $key => $value) { 
                                if(in_array($value->id, $leavedays)) {
                                    $slots[$key]->is_leave = 1;
                                }   else {
                                    $slots[$key]->is_leave = 0;
                                }
                            }

                            return response()->json(['status' => 1, 'data' => $slots, 'message' => 'Available slots']);
                        }   else {
                            return response()->json(['status' => 0, 'data' => [], 'message' => 'No Available slots']);
                        }
                        

                    }   else {
                        return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid Inputs']);
                    }
                }
            }    else {
                return response()->json([ 'status' => 0, 'data' => [], 'message' => $error]);
            }
        }   catch(\Throwable $th) {
            return response()->json(['status' => 0, 'data' => [], 'message' => $th->getMessage()]);
        }  
              
    }

    /* Get Main and Sub Category Services list for Service Provider */
    public function getServicerMainCategoryNames(Request $request) {
        try {   
            $inputJSON = file_get_contents('php://input');

            $input = json_decode($inputJSON, TRUE);

            $requiredParams = ['user_id', 'api_token'];

            $error = $this->checkParams($input, $requiredParams, $request);

            if(empty($error)) {

                $userid = ((isset($input) && isset($input['user_id']))) ? $input['user_id'] : 0; 

                $api_token = $request->header('x-api-key');
                $limit = CommonController::$page_limit;

                $mes = User::checkTokenExpiry($userid, $api_token);
                $status = $mes['status'];   $message = $mes['message'];
                if($status != 1) {
                    return response()->json([ 'status' => $status, 'data' => [], 'message' => $message]);
                }   else {
                    if($userid > 0) { 
                        $sub_category = SubCategory::has('services')->with('services')->leftjoin('em_category', 'em_category.id', '=', 'em_sub_category.category_id')
                            ->where('em_sub_category.status', 'ACTIVE')
                            //->where('em_sub_category.category_id', $category_id)
                            ->select('em_sub_category.*', 'em_category.name as category_name')
                            ->orderby('position', 'asc')
                            ->get();

                        if(!empty($sub_category)){
                            return response()->json(['status' => 1, 'message' => 'Sub Category List', 'details'=>$sub_category]);
                        }   else {
                            return response()->json(['status' => 0, 'message' => 'No Sub Category List']);
                        }
                    }   else {
                        return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid Inputs']);
                    }
                }
            }    else {
                return response()->json([ 'status' => 0, 'data' => [], 'message' => $error]);
            }
        }   catch(\Throwable $th) {
            return response()->json(['status' => 0, 'data' => [], 'message' => $th->getMessage()]);
        }  
    }

    /* Servicer get the Alert on New Leads */
    
    public function getServicerNewLead(Request $request) {
        try {   
            $inputJSON = file_get_contents('php://input');

            $input = json_decode($inputJSON, TRUE);

            $requiredParams = ['user_id', 'api_token'];

            $error = $this->checkParams($input, $requiredParams, $request);

            if(empty($error)) {

                $userid = ((isset($input) && isset($input['user_id']))) ? $input['user_id'] : 0; 

                $api_token = $request->header('x-api-key');
                $limit = CommonController::$page_limit;

                $mes = User::checkTokenExpiry($userid, $api_token);
                $status = $mes['status'];   $message = $mes['message'];
                if($status != 1) {
                    return response()->json([ 'status' => $status, 'data' => [], 'message' => $message]);
                }   else {
                    if($userid > 0) { 
                        $exuser = ServiceProvider::where('user_id', $userid)->first(); 

                        if(empty($exuser)) {
                            return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid Service Provider']);
                        } 

                        $newlead = DB::table('em_confirm_booking')->where('empl_id', $userid)
                            ->where('accept_status', 0)->where('alert_status', 1)
                            ->select('notify_from_time', 'notify_to_time', 'booking_id')
                            ->orderby('id', 'asc')->first();
                        
                        if(!empty($newlead)) {
                            $booking_id = $newlead->booking_id;
                            $notify_from_time = $newlead->notify_from_time;
                            $notify_to_time = $newlead->notify_to_time;

                            $booking = Booking::with('bookItems')
                                ->leftjoin('users', 'users.id', 'em_booking.user_id')
                                ->leftjoin('users_address', 'users_address.id', 'em_booking.user_address_id')
                                ->leftjoin('em_slots', 'em_slots.id', 'em_booking.job_slot')
                                ->where('em_booking.id', $booking_id)
                                ->select('em_booking.id','em_booking.user_id', 'em_booking.status', 'job_slot', 'job_date', 'sub_category_id', 'em_booking.user_address_id', 'em_booking.request_job_date', 'em_booking.request_job_slot', 'em_booking.reschedule_requested_date', 'users.name as user_name', 
                                    'users_address.address', 'users_address.city', 'users_address.pinarea', 'users_address.pin_code', 
                                    'em_slots.slot_name', 'em_slots.period_name', DB::Raw('"3" as credits'))
                                ->first(); 

                            $product_str = [];
                            if(!empty($booking)) { 
                                $booking_arr = $booking->toArray();
                                if(isset($booking_arr['book_items']) && is_array($booking_arr['book_items']) && count($booking_arr['book_items'])>0) {
                                    foreach ($booking_arr['book_items'] as $key => $value) {
                                        $product_str[] = $value['sub_service_name'];
                                    }
                                }
                                $booking_arr['products'] = $product_str; 
                                $booking->products = $product_str;  //echo "<pre>"; print_r($booking_arr);
                                return response()->json([ 'status' => 1, 'data' => $booking_arr, 'message' => 'New Lead Details', 
                                    'servicer_notify'=>$newlead, 'notify_to_time_sec'=>'120']);
                            } else {
                                return response()->json([ 'status' => 0, 'data' => [], 'message' => 'No New Leads']);
                            }                           
                        } else {
                            return response()->json([ 'status' => 0, 'data' => [], 'message' => 'No New Leads']);
                        }
                    }   else {
                        return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid Inputs']);
                    }
                }
            }    else {
                return response()->json([ 'status' => 0, 'data' => [], 'message' => $error]);
            }
        }   catch(\Throwable $th) {
            return response()->json(['status' => 0, 'data' => [], 'message' => $th->getMessage()]);
        }  
    }


    /* Servicer Update Status On the Alert on New Leads */
    
    public function getServicerLeadUpdateStatus(Request $request) {
        //try {   
            $inputJSON = file_get_contents('php://input');

            $input = json_decode($inputJSON, TRUE);

            $requiredParams = ['user_id', 'api_token', 'booking_id', 'status'];

            $error = $this->checkParams($input, $requiredParams, $request);

            if(empty($error)) {

                $userid = ((isset($input) && isset($input['user_id']))) ? $input['user_id'] : 0; 

                $api_token = $request->header('x-api-key');
                $limit = CommonController::$page_limit;

                $mes = User::checkTokenExpiry($userid, $api_token);
                $status = $mes['status'];   $message = $mes['message'];
                if($status != 1) {
                    return response()->json([ 'status' => $status, 'data' => [], 'message' => $message]);
                }   else {
                    if($userid > 0) { 
                        $exuser = ServiceProvider::where('user_id', $userid)->first(); 

                        if(empty($exuser)) {
                            return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid Service Provider']);
                        } 

                        $booking_id = ((isset($input) && isset($input['booking_id']))) ? $input['booking_id'] : 0; 
                        $status = ((isset($input) && isset($input['status']))) ? $input['status'] : ''; 

                        $lead = DB::table('em_confirm_booking')->where('empl_id', $userid)
                            ->where('booking_id', $booking_id)->where('accept_status', 0)
                            ->orderby('id', 'asc')->first();

                        if(!empty($lead)) {
                            $booking = DB::table('em_booking')->where('id', $booking_id)->first();
                            $booking_status = $booking->status;
                            
                            if($status == 1){
                                $otp = CommonController::generateNumericOTP(4);
                                DB::table('em_confirm_booking')->where('id', $lead->id)
                                    ->update(['accept_status'=>1, 'otp'=>$otp, 'booking_status'=> 'ACCEPTED', 'alert_status'=>0]);

                                DB::table('em_booking')->where('id', $lead->booking_id)
                                    ->update(['status'=> 'ACCEPTED', 'job_otp'=>$otp, 'accepted_date' => date('Y-m-d H:i:s'), 'service_provider_id' => $userid]);

                                if($booking_status == 'RESCHEDULE_REQUEST') {
                                    DB::table('em_booking')->where('id', $lead->booking_id)
                                    ->update(['is_rescheduled'=> 1]);
                                }
                            }  else {
                                DB::table('em_confirm_booking')->where('id', $lead->id)
                                    ->update(['accept_status'=>2, 'booking_status'=> 'REJECTED', 'alert_status'=>0]);

                                // Search for the next provider
                                $booking = DB::table('em_booking')->where('id', $booking_id)->first();
                                if(!empty($booking)) {
                                    $user_address_id = $booking->user_address_id;
                                    $booking_status = $booking->status;

                                    if($booking_status == 'RESCHEDULE_REQUEST') {
                                        DB::table('em_booking')->where('id', $lead->booking_id)
                                            ->update(['status'=> 'CANCELLED', 'cancelled_by' => 'SERVICEPROVIDER', 'service_provider_id' => $userid, 'cancelled_reason' => 'Reschedule Cancelled', 
                                                'cancelled_date' => date('Y-m-d H:i:s')
                                            ]); 

                                        $title = "Your Booking Cancelled";
                                        $message = "Your Booking ".$booking->ref_no." Cancelled";
                                            
                                        $fcmMsg = array("fcm" => array("notification" => array(
                                                "title" => $title,
                                                "body" => $message,
                                                "type" => "7",
                                              )));

                                        CommonController::push_notification($booking->user_id, $fcmMsg);

                                        return response()->json([ 'status' => 1, 'data' => [], 'message' => 'Status Updated']);
                                    }

                                    $zone_id = DB::table('users_address')->where('id', $user_address_id)->value('zone_id');
                                    $slot_id = $booking->job_slot;
                                    $job_date = $booking->job_date;
                                    $newservicer = $this->getNextProvider($userid, $zone_id, $slot_id, $job_date, $booking_id);
                                    if(count($newservicer)>0) {
                                        $emp_id = $newservicer[0];
                                        $this->assignServicerBooking($emp_id, $booking_id);

                                        $title = "New Booking Placed";
                                        $message = "New Booking ".$booking->ref_no." placed Successfully";
                                            
                                        $fcmMsg = array("fcm" => array("notification" => array(
                                                "title" => $title,
                                                "body" => $message,
                                                "type" => "2",
                                              )));

                                        CommonController::push_notification($emp_id, $fcmMsg);

                                    }   else {
                                        DB::table('em_booking')->where('id', $lead->booking_id)
                                            ->update(['status'=> 'CANCELLED', 'cancelled_reason' => 'No Service Providers Available', 
                                                'cancelled_date' => date('Y-m-d H:i:s')
                                            ]);  

                                        $title = "Your Booking Cancelled";
                                        $message = "Your Booking ".$booking->ref_no." Cancelled";
                                            
                                        $fcmMsg = array("fcm" => array("notification" => array(
                                                "title" => $title,
                                                "body" => $message,
                                                "type" => "7",
                                              )));

                                        CommonController::push_notification($booking->user_id, $fcmMsg);
                                    }
                                }
                            }                            
                            
                            return response()->json([ 'status' => 1, 'data' => [], 'message' => 'Status Updated']);

                        } else {
                            return response()->json([ 'status' => 0, 'data' => [], 'message' => 'No New Leads']);
                        }
                    }   else {
                        return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid Inputs']);
                    }
                }
            }    else {
                return response()->json([ 'status' => 0, 'data' => [], 'message' => $error]);
            }
        /*}   catch(\Throwable $th) {
            return response()->json(['status' => 0, 'data' => [], 'message' => $th->getMessage()]);
        }  */
    }

    /* Assign New Servicer for the Booking */
    public function assignServicerBooking($emp_id, $booking_id) {
        $current_date = date('Y-m-d H:i:s');
        $convertedTime = date('Y-m-d H:i:s',strtotime('+120 seconds',strtotime($current_date)));

        $confirm_booking = [
            'booking_id' => $booking_id,
            'empl_id' => $emp_id,
            'accept_status' => 0,
            'notification_sent' => 2,
            'notify_from_time' => $current_date,
            'notify_to_time' => $convertedTime,
            'booking_status' => 0,
            'alert_status' => 1,
            'created_at' => $current_date
        ];

        DB::table('em_confirm_booking')->insertGetId($confirm_booking); 
        $ref_no = DB::table('em_booking')->where('id', $booking_id)->value('ref_no');
        $title = "You Have New Booking";
        $message = "New Booking ".$ref_no;
            
        $fcmMsg = array("fcm" => array("notification" => array(
                "title" => $title,
                "body" => $message,
                "type" => "2",
              )));

        CommonController::push_notification($emp_id, $fcmMsg);
    }

    /* Seasrch for the next Employee */
    public function getNextProvider($userid, $zone_id, $slot_id, $job_date, $booking_id='') {
        $emp_id = 0;  $booking_servicer = 0;
               //$this->getZoneServicers($zone_id);
        $servicerarr = [];  $servicers_arr = [];
        if($booking_id > 0) {
            //$booking_servicer = DB::table('em_booking')->where('id', $booking_id)->value('service_provider_id');
            $booking_servicer = DB::table('em_confirm_booking')->where('booking_id', $booking_id)->select('empl_id')->get();
            if($booking_servicer->isNotEmpty()) {
                foreach($booking_servicer as $book){
                    $servicerarr[] = $book->empl_id;
                }
            }
        }

        $servicers_arr_qry = DB::table('users')
                ->leftjoin('em_service_provider', 'em_service_provider.user_id', 'users.id')
                ->whereRaw("FIND_IN_SET(".$zone_id.", em_service_provider.zone_ids)")
                ->where('users.status', 'ACTIVE')
                ->where('users.approve_status', 'APPROVED')
                ->whereNotIn('em_service_provider.user_id', $servicerarr)
                ->select('users.id')->get(); 

        if($servicers_arr_qry->isNotEmpty()) {
            foreach($servicers_arr_qry as $servicer){
                $servicers_arr[] = $servicer->id;
            }
        }


        $leave_servicers_qry = DB::table('em_servicer_leave_slots')
            ->whereRaw("FIND_IN_SET(".$slot_id.", em_servicer_leave_slots.Leave_slots)")
            ->where('leave_date', $job_date);
           // ->whereIn('servicer_id', $servicers_arr);

        if(count($servicerarr) > 0) {
            $leave_servicers_qry->whereNotIn('servicer_id', $servicerarr);
        }
        $leave_servicers = $leave_servicers_qry->select('servicer_id')->get(); 

        $leave_servicers_arr = [];
        if($leave_servicers->isNotEmpty()) {
            foreach ($leave_servicers as $key => $value) {
                $leave_servicers_arr[] = $value->servicer_id;
            }
        }

        $result = array_diff($servicers_arr,$leave_servicers_arr);
        rsort($result);
        $new_arr = [];
        if(count($result) > 0) { 

            $sentcount = $this->calculateSentCount($result, $job_date);
            if(!empty($sentcount)) {
                foreach($sentcount as $rk => $rv) {
                    $cart_details = $this->calculateFareBreakup($userid, $rv);
                    if(count($cart_details) > 0){ 
                        $new_arr[] = $rv;
                        break;
                    }
                }
            }
        }
        //echo "<pre>"; print_r($new_arr); print_r($leave_servicers_arr); print_r($servicerarr);print_r($servicers_arr);
        return $new_arr;
    }

    public function calculateSentCount($result, $job_date) {
        $servicer ='';
        if(count($result)>0) {
            $servicers = DB::table('em_confirm_booking')->where('created_at', 'like', '%'.$job_date.'%')
                ->whereIn('empl_id', $result)
                ->select(DB::RAW('count(id) as sentcount'), 'empl_id')
                ->groupBy('empl_id')
                ->orderby('sentcount', 'ASC')
                ->get();
        }
        return $servicers;
    }

    /* Servicer Update Status On the Alert on New Leads */
    
    public function getServicerLeads(Request $request) {
        try {   
            $inputJSON = file_get_contents('php://input');

            $input = json_decode($inputJSON, TRUE);

            $requiredParams = ['user_id', 'api_token', 'status'];

            $error = $this->checkParams($input, $requiredParams, $request);

            if(empty($error)) {

                $userid = ((isset($input) && isset($input['user_id']))) ? $input['user_id'] : 0; 
                $bookingstatus = ((isset($input) && isset($input['status']))) ? $input['status'] : 0; 
                // 0 -> pending , 1 -> Accepted , 2-> Rejected

                $api_token = $request->header('x-api-key');
                $limit = CommonController::$page_limit;

                $mes = User::checkTokenExpiry($userid, $api_token);
                $status = $mes['status'];   $message = $mes['message'];
                if($status != 1) {
                    return response()->json([ 'status' => $status, 'data' => [], 'message' => $message]);
                }   else {
                    if($userid > 0) { 
                        $exuser = ServiceProvider::where('user_id', $userid)->first(); 

                        if(empty($exuser)) {
                            return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid Service Provider']);
                        } 

                        $booking_status = 'PLACED';

                        if($bookingstatus == 0) {
                            $booking_status = 'PLACED';
                        }   else if($bookingstatus == 1) {
                            $booking_status = 'ACCEPTED';
                        }   else if($bookingstatus == 2) {
                            $booking_status = 'REJECTED';
                        }
                        
                        $bookings =  Booking::leftjoin('em_confirm_booking', 'em_confirm_booking.booking_id', 'em_booking.id')
                            ->where('em_confirm_booking.empl_id', $userid)
                            ->where('em_booking.status', $booking_status)
                            ->whereNotIn('em_confirm_booking.booking_status', ['REJECTED', 'EXPIRED'])
                            ->select('em_booking.*')
                            ->get();


                        if($bookings->isNotEmpty()) {                  
                            
                            return response()->json([ 'status' => 1, 'data' => $bookings, 'message' => 'Bookings List']);

                        } else {
                            return response()->json([ 'status' => 0, 'data' => [], 'message' => 'No Bookings']);
                        }
                    }   else {
                        return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid Inputs']);
                    }
                }
            }    else {
                return response()->json([ 'status' => 0, 'data' => [], 'message' => $error]);
            }
        }   catch(\Throwable $th) {
            return response()->json(['status' => 0, 'data' => [], 'message' => $th->getMessage()]);
        }  
    }

    /* Servicer View Details of the Booking by Booking Id */
    
    public function getServicerViewBooking(Request $request) {
        try {   
            $inputJSON = file_get_contents('php://input');

            $input = json_decode($inputJSON, TRUE);

            $requiredParams = ['user_id', 'api_token', 'booking_id'];

            $error = $this->checkParams($input, $requiredParams, $request);

            if(empty($error)) {

                $userid = ((isset($input) && isset($input['user_id']))) ? $input['user_id'] : 0; 
                $booking_id = ((isset($input) && isset($input['booking_id']))) ? $input['booking_id'] : 0; 

                $api_token = $request->header('x-api-key');
                $limit = CommonController::$page_limit;

                $mes = User::checkTokenExpiry($userid, $api_token);
                $status = $mes['status'];   $message = $mes['message'];
                if($status != 1) {
                    return response()->json([ 'status' => $status, 'data' => [], 'message' => $message]);
                }   else {
                    if($userid > 0) { 
                        $exuser = ServiceProvider::where('user_id', $userid)->first(); 

                        if(empty($exuser)) {
                            return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid Service Provider']);
                        } 

                        if($booking_id>0) {
                            $bookings =  Booking::with('bookItems')
                            ->leftjoin('em_confirm_booking', 'em_confirm_booking.booking_id', 'em_booking.id')
                            ->where('em_confirm_booking.empl_id', $userid)
                            ->whereNotIn('em_booking.status', ['placed'])
                            ->whereNotIn('em_confirm_booking.booking_status', ['placed'])
                            ->where('em_booking.id', $booking_id)
                            ->select('em_booking.*')
                            ->get();


                            if($bookings->isNotEmpty()) {                  
                                
                                return response()->json([ 'status' => 1, 'data' => $bookings[0], 'message' => 'Booking Details']);

                            } else {
                                return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid Bookings']);
                            }
                        }   else {
                            return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid Booking']);
                        }
                        
                        
                    }   else {
                        return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid Inputs']);
                    }
                }
            }    else {
                return response()->json([ 'status' => 0, 'data' => [], 'message' => $error]);
            }
        }   catch(\Throwable $th) {
            return response()->json(['status' => 0, 'data' => [], 'message' => $th->getMessage()]);
        }  
    }

    // Servicer Start Job
    public function postServicerJobStart(Request $request) {
        //try {   
            $inputJSON = file_get_contents('php://input');

            $input = json_decode($inputJSON, TRUE);

            $requiredParams = ['user_id', 'api_token', 'booking_id', 'otp'];

            $error = $this->checkParams($input, $requiredParams, $request);

            if(empty($error)) {

                $userid = ((isset($input) && isset($input['user_id']))) ? $input['user_id'] : 0; 
                $booking_id = ((isset($input) && isset($input['booking_id']))) ? $input['booking_id'] : 0; 
                $otp = ((isset($input) && isset($input['otp']))) ? $input['otp'] : 0; 

                $api_token = $request->header('x-api-key');
                $limit = CommonController::$page_limit;

                $mes = User::checkTokenExpiry($userid, $api_token);
                $status = $mes['status'];   $message = $mes['message'];
                if($status != 1) {
                    return response()->json([ 'status' => $status, 'data' => [], 'message' => $message]);
                }   else {
                    if($userid > 0) { 
                        $exuser = ServiceProvider::where('user_id', $userid)->first(); 

                        if(empty($exuser)) {
                            return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid Service Provider']);
                        } 

                        if($booking_id>0) {

                            if(trim($otp) == '') {
                                return response()->json(['status' => 0, 'message' => 'Please enter the Valid OTP']);
                            }

                            $job_otp = Booking::where('id', $booking_id)
                                ->where('service_provider_id', $userid)
                                ->value('job_otp');  
                            if(trim($otp) != $job_otp) {
                                return response()->json(['status' => 0, 'message' => 'Invalid OTP']);
                            }

                            $booking = Booking::where('service_provider_id', $userid)
                                ->where('id', $booking_id)->select('user_id', 'ref_no')->first();

                            if(!empty($booking)) {
                                Booking::where('service_provider_id', $userid)
                                    ->where('id', $booking_id)
                                    ->update(['status'=>'STARTED', 'job_start_date'=>date('Y-m-d H:i:s')]);

                                DB::table('em_booking_trackings')->insert([
                                    'booking_id' => $booking_id,
                                    'job_status' => 'STARTED',
                                    'job_status_value' => 'STARTED',
                                    'status_updated_date' => date('Y-m-d H:i:s'),
                                    'created_at' => date('Y-m-d H:i:s')
                                ]);

                                $fcmid = DB::table('users')->where('id', $booking->user_id)->value('fcm_id');

                                $message = 'Job has been started for the Booking: '. $booking->ref_no;

                                $title = 'Job Started';

                                $fcmMsg = array("fcm" => array("notification" => array(
                                    "title" => $title,
                                    "body" => $message,
                                    "type" => "3",
                                )));
                                CommonController::push_notification($booking->user_id, $fcmMsg); 

                                return response()->json(['status' => 1, 'message' => 'Job Started']);
                            }   else {
                                return response()->json(['status' => 0, 'message' => 'Invalid Booking']);
                            }
                        }   else {
                            return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid Booking Id']);
                        }
                    }   else {
                        return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid User Id']);
                    }
                }
            }    else {
                return response()->json([ 'status' => 0, 'data' => [], 'message' => $error]);
            }
        /*}   catch(\Throwable $th) {
            return response()->json(['status' => 0, 'data' => [], 'message' => $th->getMessage()]);
        }*/  
    }

    public function generateInvoice(Request $request)    {
        $inputJSON = file_get_contents('php://input');

        $input = json_decode($inputJSON, TRUE);

        if(!isset($input["booking_id"])) {
            return response()->json(['status' => 0, 'message' => 'Please input required parameters']);
        }

        $booking_id = $input['booking_id'];
        $booking_details = DB::table('em_booking')->where('id', $booking_id)->first();
        $fees = DB::table('em_booking_additional_fees')->where('booking_id', $booking_id)->get();

        if (!empty($booking_details)) {
            /*if($booking_details->status == 'STARTED') {
                return response()->json(['status' => "0", 'message' => 'Please Update the Booking Status']);
            }*/
            return response()->json(['status' => "1", 'message' => 'Booking Details', 'data' => $booking_details, 'additional_fees' => $fees]);
        } else {

            return response()->json(['status' => "0", 'message' => 'No Booking Details']);
        }
    }

    // Servicer End Job
    public function postServicerJobEnd(Request $request)    {
        try {   
            /*$inputJSON = file_get_contents('php://input');

            $input = json_decode($inputJSON, TRUE);*/

            $input  = $request->all();

            $requiredParams = ['user_id', 'api_token', 'booking_id'];  //, 'additional_fees'

            $error = $this->checkParams($input, $requiredParams, $request, true);

            if(empty($error)) {

                $userid = ((isset($input) && isset($input['user_id']))) ? $input['user_id'] : 0; 
                $booking_id = ((isset($input) && isset($input['booking_id']))) ? $input['booking_id'] : 0; 
                $additional_fees = ((isset($input) && isset($input['additional_fees']))) ? $input['additional_fees'] : ''; 
                $additional_fees = json_decode($additional_fees, true); 
                $job_description = ((isset($input) && isset($input['job_description']))) ? $input['job_description'] : ''; 

                $api_token = $request->header('x-api-key');
                $limit = CommonController::$page_limit;

                $mes = User::checkTokenExpiry($userid, $api_token);
                $status = $mes['status'];   $message = $mes['message'];
                if($status != 1) {
                    return response()->json([ 'status' => $status, 'data' => [], 'message' => $message]);
                }   else {
                    if($userid > 0) { 
                        $exuser = ServiceProvider::where('user_id', $userid)->first(); 

                        if(empty($exuser)) {
                            return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid Service Provider']);
                        } 

                        if($booking_id>0) {

                            $booking = Booking::where('service_provider_id', $userid)
                                ->where('id', $booking_id)->select('user_id', 'ref_no')->first();

                            if(!empty($booking)) {

                                DB::table('em_booking_additional_fees')
                                    ->where('booking_id', $booking_id)
                                    ->where('service_provider_id', $userid)
                                    ->delete();

                                /* Add Aditional Fees */
                                $service_provider_id = $userid;
                                if(is_array($additional_fees) && count($additional_fees)>0) { 
                                    $fees = $additional_fees;
                                    
                                    $total_fees = 0;
                                    foreach ($fees as $key => $value) {
                                        
                                        DB::table('em_booking_additional_fees')
                                            ->insert([
                                                'booking_id' => $booking_id,
                                                'service_provider_id' => $service_provider_id, 
                                                'fees_name' => $value['fees_type'],
                                                'fees_value' => $value['price'],
                                                'status' => 'ACTIVE',
                                                'updated_at' => date('Y-m-d H:i:s')
                                            ]);
                                        
                                        $total_fees += $value['price'];
                                    }

                                    $book = DB::table('em_booking')
                                        ->where('id', $booking_id)
                                        ->where('service_provider_id', $service_provider_id)
                                        ->select('sub_total', 'sub_total_amount', 'tax_percentage', 'total_amount', 'coupon_id', 'coupon_amount', 'coupon_code', 'additional_charge')->first();

                                    $tax_percentage = $book->tax_percentage;
                                    $subtotal = $book->sub_total_amount;
                                    $sub_total_amount = $book->sub_total_amount;

                                    $subtotal = $subtotal + $total_fees;
                                    $tax_total = $subtotal * $tax_percentage / 100;

                                    $total_amount = $book->total_amount;
                                    $coupon_amount = $book->coupon_amount;
                                    $coupon_code  = $book->coupon_code;

                                    $additional_charge = $book->additional_charge;
                                    if($additional_charge > 0) {}
                                    else { $additional_charge = 0; }

                                    if(empty($coupon_amount)) $coupon_amount = 0;

                                    $discount = 0;
                                    $coupon_id = $book->coupon_id;
                                    if($book->coupon_id > 0) {
                                        $coupon = Coupon::find($book->coupon_id);
                                        if($subtotal > $coupon->min_order_amount) {  
                                            $discount = 0;
                                            if($coupon->type == 'COUPON') {
                                                $discount = $subtotal * $coupon->coupon_percentage / 100;
                                            }   else {
                                                $discount = $coupon->coupon_value;
                                            }
                                            if($discount > $subtotal) {
                                                $coupon_id = 0; $discount = NULL; $coupon_code = NULL;
                                            }
                                        } else {
                                            $coupon_id = 0; $discount = NULL; $coupon_code = NULL;
                                        }
                                    }                   
                                    $coupon_amount = $discount;
 
                                    DB::table('em_booking')
                                        ->where('id', $booking_id)
                                        ->where('service_provider_id', $service_provider_id)
                                        ->update([
                                            'tax_total' => $tax_total,
                                            'sub_total' => $subtotal - $tax_total - $coupon_amount,
                                            'total_amount' => $subtotal - $coupon_amount + $additional_charge,
                                            'sub_total_amount' => $subtotal,
                                            'updated_at' => date('Y-m-d H:i:s'),
                                            'coupon_id' => $coupon_id,
                                            'coupon_amount' =>  $coupon_amount,
                                            'coupon_code' => $coupon_code,

                                        ]);

            
                                }/*   else {
                                    return response()->json(['status' => 0, 'message' => 'Please input valid Fees Details']);
                                }*/

                                /* Add Additional Fees */

                                $jobstatus = 'ENDED';
                                DB::table('em_booking_trackings')->insert([
                                    'booking_id' => $booking_id,
                                    'job_status' => $jobstatus,
                                    'job_status_value' => 'Job '.$jobstatus,
                                    'status_updated_date' => date('Y-m-d H:i:s'),
                                    'created_at' => date('Y-m-d H:i:s')
                                ]);


                                /* Job Decscription and Images */
                                $imageslist = [];  $imageslist_str = '';
                                $accepted_formats = ['jpeg', 'jpg', 'png'];
                                $images = $request->file('job_images');
                                if (!empty($images)) {
                                    foreach ($images as $key => $image) {
                                        $ext = $image->getClientOriginalExtension();

                                        if(!in_array($ext, $accepted_formats)) {
                                            return response()->json(['status' => 0, 'message' => 'File Format Wrong.Please upload PNG,JPEG,JPG']);
                                        }

                                        $jobImage = 'services-' .rand().time() . '.' . $image->getClientOriginalExtension();

                                        $destinationPath = public_path('/uploads/jobimages');

                                        $image->move($destinationPath, $jobImage);

                                        $imageslist[] = $jobImage;
                                    }
                                    if(count($imageslist)>0) {
                                        $imageslist_str = implode(',',$imageslist);
                                    }
                                    
                                }

                                Booking::where('service_provider_id', $userid)
                                    ->where('id', $booking_id)
                                    ->update(['status'=>$jobstatus, 'job_ended_date' => date('Y-m-d H:i:s'),
                                              'servicer_description'=>$job_description, 'servicer_images'=>$imageslist_str
                                    ]);
 
                                $fcmid = DB::table('users')->where('id', $booking->user_id)->value('fcm_id');

                                $message = 'Job Status has been updated for the Booking: '. $booking->ref_no.' as '.$jobstatus;

                                $title = 'Job '.$jobstatus;

                                $fcmMsg = array("fcm" => array("notification" => array(
                                    "title" => $title,
                                    "body" => $message,
                                    "type" => "4",
                                )));
                                CommonController::push_notification($booking->user_id, $fcmMsg); 
                            }   else {
                                return response()->json(['status' => 0, 'message' => 'Invalid Booking']);
                            }

                            $booking_details = Booking::where('id', $booking_id)->where('service_provider_id', $userid)->first();
                            if (!empty($booking_details)) {
                                return response()->json(['status' => 1, 'message' => 'Job Ended', 'data' => $booking_details]);
                            } else {

                                return response()->json(['status' => 0, 'message' => 'No Booking Details']);
                            }
                        }   else {
                            return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid Inputs']);
                        }
                    }   else {
                        return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid User Id']);
                    }
                }
            }    else {
                return response()->json([ 'status' => 0, 'data' => [], 'message' => $error]);
            }
        }   catch(\Throwable $th) {
            return response()->json(['status' => 0, 'data' => [], 'message' => $th->getMessage()]);
        } 

    }

    public function getJobStatus(Request $request) {
        try {   
            $inputJSON = file_get_contents('php://input');

            $input = json_decode($inputJSON, TRUE);

            $requiredParams = ['user_id', 'api_token'];

            $error = $this->checkParams($input, $requiredParams, $request);

            if(empty($error)) {

                $userid = ((isset($input) && isset($input['user_id']))) ? $input['user_id'] : 0;  

                $api_token = $request->header('x-api-key');
                $limit = CommonController::$page_limit;

                $mes = User::checkTokenExpiry($userid, $api_token);
                $status = $mes['status'];   $message = $mes['message'];
                if($status != 1) {
                    return response()->json([ 'status' => $status, 'data' => [], 'message' => $message]);
                }   else {
                    if($userid > 0) {  
                        $jobstatus = JobStatus::where('status', 'ACTIVE')->get();

                        if($jobstatus->isNotEmpty()){
                            return response()->json(['status' => 1, 'message' => 'Job Status List', 'details'=>$jobstatus]);
                        }   else {
                            return response()->json(['status' => 0, 'message' => 'No Job Status List']);
                        }
                    }   else {
                        return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid Inputs']);
                    }
                }
            }    else {
                return response()->json([ 'status' => 0, 'data' => [], 'message' => $error]);
            }
        }   catch(\Throwable $th) {
            return response()->json(['status' => 0, 'data' => [], 'message' => $th->getMessage()]);
        } 
        
    }

    /*  Get Rate Card */
    public function getRateCard(Request $request) {
        try {   
            $inputJSON = file_get_contents('php://input');

            $input = json_decode($inputJSON, TRUE);

            $requiredParams = ['user_id', 'api_token'];

            $error = $this->checkParams($input, $requiredParams, $request);

            if(empty($error)) {

                $userid = ((isset($input) && isset($input['user_id']))) ? $input['user_id'] : 0;  

                $api_token = $request->header('x-api-key');
                $limit = CommonController::$page_limit;

                $mes = User::checkTokenExpiry($userid, $api_token);
                $status = $mes['status'];   $message = $mes['message'];
                if($status != 1) {
                    return response()->json([ 'status' => $status, 'data' => [], 'message' => $message]);
                }   else {
                    if($userid > 0) {  
                        $ratecard = DB::table('em_admin_settings')->where('id', 1)->value('ratecard');

                        if(!empty($ratecard)){
                            $ratecard = config("constants.APP_IMAGE_URL").'image/ratecard/'.$ratecard;
                            return response()->json(['status' => 1, 'message' => 'Rate Card', 'details'=>$ratecard]);
                        }   else {
                            return response()->json(['status' => 0, 'message' => 'No Ratecard Updated']);
                        }
                    }   else {
                        return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid Inputs']);
                    }
                }
            }    else {
                return response()->json([ 'status' => 0, 'data' => [], 'message' => $error]);
            }
        }   catch(\Throwable $th) {
            return response()->json(['status' => 0, 'data' => [], 'message' => $th->getMessage()]);
        } 
        
    }

    // Servicer Update Job Status
    public function postServicerJobStatusUpdate(Request $request) {

        try {   
            $inputJSON = file_get_contents('php://input');

            $input = json_decode($inputJSON, TRUE);

            $requiredParams = ['user_id', 'api_token', 'booking_id', 'job_status_id'];

            $error = $this->checkParams($input, $requiredParams, $request);

            if(empty($error)) {

                $userid = ((isset($input) && isset($input['user_id']))) ? $input['user_id'] : 0; 
                $booking_id = ((isset($input) && isset($input['booking_id']))) ? $input['booking_id'] : 0; 
                $job_status_id = ((isset($input) && isset($input['job_status_id']))) ? $input['job_status_id'] : 0; 

                $api_token = $request->header('x-api-key');
                $limit = CommonController::$page_limit;

                $mes = User::checkTokenExpiry($userid, $api_token);
                $status = $mes['status'];   $message = $mes['message'];
                if($status != 1) {
                    return response()->json([ 'status' => $status, 'data' => [], 'message' => $message]);
                }   else {
                    if($userid > 0) { 
                        $exuser = ServiceProvider::where('user_id', $userid)->first(); 

                        if(empty($exuser)) {
                            return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid Service Provider']);
                        } 

                        $service_provider_id = $userid;

                        if($job_status_id > 0) {
                            $job_status = JobStatus::find($job_status_id);
                            if(!empty($job_status)) {

                                $booking = Booking::where('service_provider_id', $service_provider_id)
                                    ->where('id', $booking_id)->select('user_id', 'ref_no')->first();

                                if(!empty($booking)) {

                                    DB::table('em_booking_trackings')->insert([
                                        'booking_id' => $booking_id,
                                        'job_status' => $job_status->status_value,
                                        'job_status_value' => $job_status->status_description,
                                        'status_updated_date' => date('Y-m-d H:i:s'),
                                        'created_at' => date('Y-m-d H:i:s')
                                    ]);

                                    Booking::where('service_provider_id', $service_provider_id)
                                        ->where('id', $booking_id)
                                        ->update(['status'=>$job_status->status_value, 'job_start_date'=>date('Y-m-d H:i:s')]);

                                    $fcmid = DB::table('users')->where('id', $booking->user_id)->value('fcm_id');

                                    $message = 'Job Status has been updated for the Booking: '. $booking->ref_no.' as '.$job_status->status_description;

                                    $title = 'Job Status Update';

                                    $fcmMsg = array("fcm" => array("notification" => array(
                                        "title" => $title,
                                        "body" => $message,
                                        "type" => "5",
                                    )));
                                    CommonController::push_notification($booking->user_id, $fcmMsg); 

                                    return response()->json(['status' => 1, 'message' => 'Job Status Updated']);
                                }   else {
                                    return response()->json(['status' => 0, 'message' => 'Invalid Booking']);
                                }
                            }
                        }   else {
                            return response()->json(['status' => 0, 'message' => 'Please enter the valid Job Status']);
                        }

                    }   else {
                        return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid Inputs']);
                    }
                }
            }    else {
                return response()->json([ 'status' => 0, 'data' => [], 'message' => $error]);
            }
        }   catch(\Throwable $th) {
            return response()->json(['status' => 0, 'data' => [], 'message' => $th->getMessage()]);
        }   
        
    }

    /* Get Servicer Bookings */
    public function getServicerBookings(Request $request) {
        try {   
            $inputJSON = file_get_contents('php://input');

            $input = json_decode($inputJSON, TRUE);

            $requiredParams = ['user_id', 'api_token'];

            $error = $this->checkParams($input, $requiredParams, $request);

            if(empty($error)) {

                $userid = ((isset($input) && isset($input['user_id']))) ? $input['user_id'] : 0; 
                $page_no = ((isset($input) && isset($input['page_no']))) ? $input['page_no'] : 0; 
                $booking_status = isset($input["status"]) ? $input["status"] : 0;
                if(empty($booking_status)) {
                    $booking_status = 0; 
                }
                if(empty($page_no)) {
                    $page_no = 0;
                } 
                $api_token = $request->header('x-api-key');
                $limit = CommonController::$page_limit;

                $mes = User::checkTokenExpiry($userid, $api_token);
                $status = $mes['status'];   $message = $mes['message'];
                if($status != 1) {
                    return response()->json([ 'status' => $status, 'data' => [], 'message' => $message]);
                }   else {
                    if($userid > 0) { 
                        //DB::RAW('group_concat(em_slots.slot_name) as name')

                        //if($booking_status != 4) {

                            $bookings_qry = Booking::select('em_booking.*','em_slots.slot_name as name', DB::RAW('(SELECT name FROM em_sub_category mcc WHERE mcc.id = em_booking.`sub_category_id`) AS main_category_name'))
                                ->leftjoin('em_slots', \DB::raw("FIND_IN_SET(em_slots.id, em_booking.job_slot)"),">",\DB::raw("'0'"));
                                //->where('em_booking.service_provider_id', $userid);

                            $orderby = 'em_booking.id'; $ordermode = 'DESC';
                            if($booking_status == 0) {
                                $bookings_qry->whereIn('em_booking.status', ['PENDING','RESCHEDULE_REQUEST'])
                                    ->where('em_booking.service_provider_id', $userid);
                            }   else if( $booking_status == 1) {
                                $bookings_qry->whereIn('em_booking.status', ['STARTED','INPROGRESS','UNABLETOREPAIR','ENDED', 'UNABLETOCOMPLETE']);
                                $bookings_qry->where('payment_status', 'PENDING')
                                    ->where('em_booking.service_provider_id', $userid);
                                $orderby = 'payment_status'; $ordermode = 'DESC';
                            }   else if( $booking_status == 2) {

                                $bookings_qry->whereRaw("((`em_booking`.`status` in ('COMPLETED') and `payment_status` = 'PAID') or (`em_booking`.`status` in ('ABANDONED') and `payment_status` = 'PENDING'))")
                                    ->where('em_booking.service_provider_id', $userid);
                            }   else if( $booking_status == 3) {
                                $bookings_qry->leftjoin('em_confirm_booking', 'em_confirm_booking.booking_id', 'em_booking.id')
                                    ->where(function($query) use($userid) {
                                        $query->where('em_confirm_booking.booking_status', 'EXPIRED')
                                            ->where('em_confirm_booking.empl_id', $userid);
                                    })->orwhere(function($query) use($userid) {
                                        $query->where('em_booking.service_provider_id', $userid)
                                            ->where('em_booking.status', 'CANCELLED');
                                    });
                                    
                                //$bookings_qry->where('em_booking.status', 'CANCELLED');
                            }   else {
                                $bookings_qry->where('em_booking.service_provider_id', $userid);;
                            }

                               // ->groupby('em_booking.id')
                            $bookings =   $bookings_qry->orderby($orderby,$ordermode)
                                ->skip($page_no)->limit($limit)
                                ->get();
                        /*} else if($booking_status == 4) {
                            $bookings = Booking::leftjoin('em_confirm_booking', 'em_confirm_booking.booking_id', 'em_booking.id')
                                ->where('empl_id', $userid)
                                ->where('booking_status', 'EXPIRED')
                                ->orderby('em_booking.id','desc')
                                ->skip($page_no)->limit($limit)
                                ->get();
                        }*/
                        
                        if($bookings->isNotEmpty()) {
                            /*$bookings = $bookings->toArray();
                            foreach ($bookings as $key => $bookingrows) {
                                 $bookingsArray[] = array(
                                    "type" => 1,
                                    "booking_id" => (int) $bookingrows['id'],
                                    "booking_ref_no" => $bookingrows['ref_no'],
                                    "estimate" => (int) $bookingrows['total_amount'],
                                    "date" => $bookingrows['job_date'],
                                    "slot" => $bookingrows['name'],
                                    "booking_status" => $bookingrows['status'],
                                    "servicer_comment" => '', //$bookingrows['servicer_comment'],
                                    "servicer_status" => '', //$bookingrows['servicer_status'],
                                    "payment" => $bookingrows['payment_status'],
                                    "payment_type" => $bookingrows['payment_mode'],
                                    "updated" => $bookingrows['updated_at'],
                                    "amount_payable" => $bookingrows['total_amount'],
                                    "hour_type" => '', //$bookingrows['hour_type'],
                                    "main_category_name" => $bookingrows['main_category_name'],
                                    
                                );
                            }*/
                         
                            return response()->json([ 'status' => 1, 'data' => $bookings, 'message' => 'Bookings List']);
                        }   else {
                            return response()->json([ 'status' => 0, 'data' => [], 'message' => 'No Bookings']);
                        } 
                    }   else {
                        return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid Inputs']);
                    }
                }
            }    else {
                return response()->json([ 'status' => 0, 'data' => [], 'message' => $error]);
            }
        }   catch(\Throwable $th) {
            return response()->json(['status' => 0, 'data' => [], 'message' => $th->getMessage()]);
        }  
    }

    /* Get Servicer Bookings */
    public function getServicerDateBookings(Request $request) {
        try {   
            $inputJSON = file_get_contents('php://input');

            $input = json_decode($inputJSON, TRUE);

            $requiredParams = ['user_id', 'api_token'];

            $error = $this->checkParams($input, $requiredParams, $request);

            if(empty($error)) {

                $userid = ((isset($input) && isset($input['user_id']))) ? $input['user_id'] : 0; 
                $page_no = ((isset($input) && isset($input['page_no']))) ? $input['page_no'] : 0; 
                $booking_status = isset($input["status"]) ? $input["status"] : 0;
                if(empty($booking_status)) {
                    $booking_status = 0; 
                }
                if(empty($page_no)) {
                    $page_no = 0;
                } 
                $api_token = $request->header('x-api-key');
                $limit = CommonController::$page_limit;

                $mes = User::checkTokenExpiry($userid, $api_token);
                $status = $mes['status'];   $message = $mes['message'];
                if($status != 1) {
                    return response()->json([ 'status' => $status, 'data' => [], 'message' => $message]);
                }   else {
                    if($userid > 0) { 
                        //DB::RAW('group_concat(em_slots.slot_name) as name')

                        //if($booking_status != 4) {

                            $bookings_qry = Booking::select('em_booking.*','em_slots.slot_name as name', DB::RAW('(SELECT name FROM em_sub_category mcc WHERE mcc.id = em_booking.`sub_category_id`) AS main_category_name'))
                                ->leftjoin('em_slots', \DB::raw("FIND_IN_SET(em_slots.id, em_booking.job_slot)"),">",\DB::raw("'0'"))
                                ->where('em_booking.service_provider_id', $userid);

                            $orderby = 'em_booking.id'; $ordermode = 'DESC';
                            if($booking_status == 0) { // ALL
                            }   else if( $booking_status == 1) { // Today
                                $bookings_qry->where('em_booking.job_date', 'like', '%'.date('Y-m-d').'%');
                            }   else if( $booking_status == 2) { // Tomorrow
                                $bookings_qry->where('em_booking.job_date', 'like', '%'.date('Y-m-d', strtotime('+1 Day')).'%');
                            }   else if( $booking_status == 3) { // This Week
                                $bookings_qry->where('em_booking.job_date', '>=', date("Y-m-d", strtotime('monday this week')))
                                    ->where('em_booking.job_date', '<=', date("Y-m-d", strtotime('sunday this week')));
                            }   else { 
                            }
 
                            $bookings =   $bookings_qry->orderby($orderby,$ordermode)
                                ->skip($page_no)->limit($limit)
                                ->get(); 
                        
                        if($bookings->isNotEmpty()) { 
                            return response()->json([ 'status' => 1, 'data' => $bookings, 'message' => 'Bookings List']);
                        }   else {
                            return response()->json([ 'status' => 0, 'data' => [], 'message' => 'No Bookings']);
                        } 
                    }   else {
                        return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid Inputs']);
                    }
                }
            }    else {
                return response()->json([ 'status' => 0, 'data' => [], 'message' => $error]);
            }
        }   catch(\Throwable $th) {
            return response()->json(['status' => 0, 'data' => [], 'message' => $th->getMessage()]);
        }  
    }

    public function postUserRequestReschedule(Request $request) {
        try {   
            $inputJSON = file_get_contents('php://input');

            $input = json_decode($inputJSON, TRUE);

            $requiredParams = ['user_id', 'api_token', 'booking_id', 'job_date', 'job_slot'];

            $error = $this->checkParams($input, $requiredParams, $request);

            if(empty($error)) {

                $userid = ((isset($input) && isset($input['user_id']))) ? $input['user_id'] : 0; 
                $booking_id = ((isset($input) && isset($input['booking_id']))) ? $input['booking_id'] : 0; 
                $job_date = isset($input["job_date"]) ? $input["job_date"] : '';
                $job_slot = isset($input["job_slot"]) ? $input["job_slot"] : 0;

                if(empty($job_date)) {
                    $job_date = ''; 
                }
                if(empty($job_slot)) {
                    $job_slot = 0;
                } 

                $api_token = $request->header('x-api-key');
                $limit = CommonController::$page_limit;

                $mes = User::checkTokenExpiry($userid, $api_token);
                $status = $mes['status'];   $message = $mes['message'];
                if($status != 1) {
                    return response()->json([ 'status' => $status, 'data' => [], 'message' => $message]);
                }   else {
                    if($userid > 0) { 
                        $user_id = $userid; 

                        $exuser = User::where('id', $user_id)->first();
                        if(!empty($exuser)) {
                            $booking = Booking::where('id', $booking_id)->where('user_id', $user_id)->first();
                            
                            if(!empty($booking)) {
                                if($booking->is_rescheduled == 1) {
                                    return response()->json(['status' => 0, 'message' => 'Reschedule Request made already']);
                                }

                                if(empty($job_date)) {
                                    return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Please enter valid Job Date and Slot']);
                                }

                                $date = date('Y-m-d');
                                if (strtotime($job_date) < strtotime($date)) {
                                    return response()->json([ 'status' => 0, 'data' => [], 'message' => "Past date is supplied"]);
                                }


                                if(empty($booking->request_job_date) || ($booking->request_job_date == NULL)) {
                                    Booking::where('id', $booking_id)->where('user_id', $user_id)
                                        ->update([
                                            'job_date' =>date('Y-m-d', strtotime($job_date)),
                                            'job_slot' => $job_slot,
                                            'request_job_date' => date('Y-m-d', strtotime($job_date)),
                                            'request_job_slot' => $job_slot,
                                            'old_status' => $booking->status,
                                            'reschedule_requested_date' => date('Y-m-d H:i:s'),
                                            'status' => 'RESCHEDULE_REQUEST'
                                        ]);

                                    if($booking->service_provider_id > 0) {
                                        $current_date = date('Y-m-d H:i:s');
                                        $convertedTime = date('Y-m-d H:i:s',strtotime('+120 seconds',strtotime($current_date)));
                                        DB::table('em_confirm_booking')->where('empl_id', $booking->service_provider_id)
                                            ->where('booking_id', $booking_id)
                                            ->update(['accept_status'=>0, 'alert_status'=>1, 
                                                'booking_status'=>'RESCHEDULE_REQUEST',
                                                'notify_from_time'=>$current_date,
                                                'notify_to_time'=>$convertedTime,
                                                'updated_at' => date('Y-m-d H:i:s')]);

                                        $fcmid = DB::table('users')->where('id', $booking->service_provider_id)->value('fcm_id');

                                        $message = 'Reschedule Request on Booking '. $booking->ref_no . ' has been Placed.';

                                        $title = 'Reschedule Request on Booking'; 

                                        $fcmMsg = array("fcm" => array("notification" => array(
                                            "title" => $title,
                                            "body" => $message,
                                            "type" => "6",
                                            'booking_id' => $booking_id
                                          )));

                                        CommonController::push_notification($booking->service_provider_id, $fcmMsg, 0, $fcmid);
                                         
                                    }
                                    
                                
                                    return response()->json(['status' => 1, 'message' => 'Reschedule Request sent to the Service Provider Successfully', 'details' => []]);
                                }   else {
                                    return response()->json(['status' => 0, 'message' => 'Reschedule Request made already']);
                                }
                                
                            }   else {
                                return response()->json(['status' => 0, 'message' => 'Invalid Booking']);
                            }
                        }   else {
                            return response()->json(['status' => 0, 'message' => 'Invalid User']);
                        }
                    }else {
                        return response()->json(['status' => 0, 'message' => 'Invalid User']);
                    }
                }
            }    else {
                return response()->json([ 'status' => 0, 'data' => [], 'message' => $error]);
            }
        }   catch(\Throwable $th) {
            return response()->json(['status' => 0, 'data' => [], 'message' => $th->getMessage()]);
        }  
    }

    // User Make the Payment 
    public function postUserMakePayment(Request $request)    {
        try {   
            $inputJSON = file_get_contents('php://input');

            $input = json_decode($inputJSON, TRUE);

            $requiredParams = ['user_id', 'api_token', 'booking_id', 'mode', 'transaction_id'];

            $error = $this->checkParams($input, $requiredParams, $request);

            if(empty($error)) {

                $userid = ((isset($input) && isset($input['user_id']))) ? $input['user_id'] : 0; 
                $booking_id = ((isset($input) && isset($input['booking_id']))) ? $input['booking_id'] : 0; 
                $payment_mode = isset($input["mode"]) ? $input["mode"] : '';
                $transaction_id = ((isset($input) && isset($input['transaction_id']))) ? $input['transaction_id'] : ''; 

                $api_token = $request->header('x-api-key');
                $limit = CommonController::$page_limit;

                $mes = User::checkTokenExpiry($userid, $api_token);
                $status = $mes['status'];   $message = $mes['message'];
                if($status != 1) {
                    return response()->json([ 'status' => $status, 'data' => [], 'message' => $message]);
                }   else {
                    if($userid > 0 && $booking_id > 0 && !empty($payment_mode)) {

                        Booking::where('user_id', $userid)
                            ->where('id', $booking_id)
                            ->update(['status'=>'COMPLETED', 'payment_mode'=>$payment_mode, 'payment_status' =>'PAID', 'payment_date' => date('Y-m-d H:i:s'), 'transaction_id'=>$transaction_id]);

                        DB::table('em_booking_trackings')->insert([
                            'booking_id' => $booking_id,
                            'job_status' => 'PAID',
                            'job_status_value' => 'PAID '. $payment_mode,
                            'status_updated_date' => date('Y-m-d H:i:s'),
                            'created_at' => date('Y-m-d H:i:s')
                        ]);

                        $this->commissionAmountDeduction($booking_id);

                        return response()->json(['status' => 1, 'message' => 'Booking Payment Updated']);
                    
                    }   else {
                        return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid Inputs']);
                    }
                }
            }    else {
                return response()->json([ 'status' => 0, 'data' => [], 'message' => $error]);
            }
        }   catch(\Throwable $th) {
            return response()->json(['status' => 0, 'data' => [], 'message' => $th->getMessage()]);
        }   
 
    }

    // User Rate the Booking 
    public function postUserRateBooking(Request $request)    {
        try {   
            $inputJSON = file_get_contents('php://input');

            $input = json_decode($inputJSON, TRUE);

            $requiredParams = ['user_id', 'api_token', 'booking_id', 'rate_value', 'rate_comments'];

            $error = $this->checkParams($input, $requiredParams, $request);

            if(empty($error)) {

                $userid = ((isset($input) && isset($input['user_id']))) ? $input['user_id'] : 0; 
                $booking_id = ((isset($input) && isset($input['booking_id']))) ? $input['booking_id'] : 0; 
                $rate_value = ((isset($input) && isset($input['rate_value']))) ? $input['rate_value'] : 0; 
                $rate_comments = ((isset($input) && isset($input['rate_comments']))) ? $input['rate_comments'] :''; 
                
                $api_token = $request->header('x-api-key');
                $limit = CommonController::$page_limit;

                $mes = User::checkTokenExpiry($userid, $api_token);
                $status = $mes['status'];   $message = $mes['message'];
                if($status != 1) {
                    return response()->json([ 'status' => $status, 'data' => [], 'message' => $message]);
                }   else {
                    if($userid > 0 && $booking_id > 0 && $rate_value > 0) {

                        Booking::where('user_id', $userid)
                            ->where('id', $booking_id)
                            ->update(['rating'=>$rate_value, 'rating_comment'=>$rate_comments, 'rated_date' => date('Y-m-d H:i:s')]);
 
                        return response()->json(['status' => 1, 'message' => 'Booking Rating Updated']);
                    
                    }   else {
                        return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid Inputs']);
                    }
                }
            }    else {
                return response()->json([ 'status' => 0, 'data' => [], 'message' => $error]);
            }
        }   catch(\Throwable $th) {
            return response()->json(['status' => 0, 'data' => [], 'message' => $th->getMessage()]);
        }   
 
    }

    // User Submit Contact Us
    public function postUserContactUs(Request $request)    {
        try {   
            $inputJSON = file_get_contents('php://input');

            $input = json_decode($inputJSON, TRUE);

            $requiredParams = ['user_id', 'api_token', 'name', 'mobile', 'message', 'country_code'];

            $error = $this->checkParams($input, $requiredParams, $request);

            if(empty($error)) {

                $userid = ((isset($input) && isset($input['user_id']))) ? $input['user_id'] : 0; 
                $name = ((isset($input) && isset($input['name']))) ? $input['name'] : ''; 
                $country_code = ((isset($input) && isset($input['country_code']))) ? $input['country_code'] : ''; 
                $mobile = ((isset($input) && isset($input['mobile']))) ? $input['mobile'] : ''; 
                $message = ((isset($input) && isset($input['message']))) ? $input['message'] : ''; 

                $api_token = $request->header('x-api-key');
                $limit = CommonController::$page_limit;

                $mes = User::checkTokenExpiry($userid, $api_token);
                $status = $mes['status'];   $message = $mes['message'];
                if($status != 1) {
                    return response()->json([ 'status' => $status, 'data' => [], 'message' => $message]);
                }   else {

                    DB::table('em_support_request')->insert([
                        'code' => 'HA'.time(),
                        'user_id' => $userid,
                        'name' => $name,
                        'country_code' => $country_code,
                        'mobile' => $mobile,
                        'email' => '',
                        'message' => $message, 
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
 
                    return response()->json(['status' => 1, 'message' => 'Details Submitted']);
                     
                }
            }    else {
                return response()->json([ 'status' => 0, 'data' => [], 'message' => $error]);
            }
        }   catch(\Throwable $th) {
            return response()->json(['status' => 0, 'data' => [], 'message' => $th->getMessage()]);
        }   
 
    }

    // commissionAmountDeduction
    public function commissionAmountDeduction($booking_id){
        $admincommission = 2;

        $admincommission = DB::table('em_admin_settings')->where('id', 1)->value('admin_commission');

        if($admincommission == 0) {
          $admincommission = 2;
        }

        $payoutcommission = 0;
  
        $booking = DB::table('em_booking')->where('id',$booking_id)->get();

        foreach($booking as $book){

           $sub_category_id = $book->sub_category_id;

           if($sub_category_id > 0) {
                $admincommission = DB::table('em_sub_category')->where('id', $sub_category_id)->value('commission_percentage');
           }

           $booking_value = $book->sub_total;

           $commissionamount = $booking_value * $admincommission /100;

           $commision_remaining_amount  = $booking_value - $commissionamount;

           $payoutamount = $commision_remaining_amount * $payoutcommission /100;

           $total_amount  = $commision_remaining_amount - $payoutamount;

           $data = [
               'code'=>time(),
               'commission_percentage' => $admincommission,
               'payout_percentage' => $payoutcommission,
               'service_provider_id'=> $book->service_provider_id,
               'booking_id'=>$book->id,
               'user_id'=>$book->user_id,
               'booking_amount'=>$book->sub_total,
               'commission_amount'=>$commissionamount,
               'commision_remaining_amount'=>$commision_remaining_amount,
               'payout_amount'=>$payoutamount,
               'total_amount'=>$total_amount,
               'payment_date'=>date('Y-m-d'),
               'created_at'=>date('Y-m-d H:i:s')
            ];

            $payment = DB::table('em_service_provider_payments')->insert($data);
        
       }

       if(!empty($payment)){

            return true;

        }else{

            return false;
        }

   }



    // Get the Current Bookings Service providers Available Days 

    public function getUserServicerAvailableDays(Request $request)    {

        try {   
            $inputJSON = file_get_contents('php://input');

            $input = json_decode($inputJSON, TRUE);

            $requiredParams = ['user_id', 'api_token', 'booking_id'];

            $error = $this->checkParams($input, $requiredParams, $request);

            if(empty($error)) {

                $userid = ((isset($input) && isset($input['user_id']))) ? $input['user_id'] : 0; 
                $booking_id = ((isset($input) && isset($input['booking_id']))) ? $input['booking_id'] : 0; 

                $api_token = $request->header('x-api-key');

                $mes = User::checkTokenExpiry($userid, $api_token);
                $status = $mes['status'];   $message = $mes['message'];
                if($status != 1) {
                    return response()->json([ 'status' => $status, 'data' => [], 'message' => $message]);
                }   else {
                    if($userid > 0) { 

                        $date = date('Y-m-d', strtotime(date('Y-m-d').' +1 day'));
                        
                        if($booking_id>0) {
                            $service_provider_id = Booking::where('id', $booking_id)->where('user_id', $userid)->value('service_provider_id');
                        }                        
                        
                        /*if($service_provider_id>0) {
                            $servicers = ServiceProvider::where('user_id', $service_provider_id)->first();
                            if(!empty($servicers)) {*/
                                $start_date = date("Y-m-d", strtotime($date));  
                                $from_date = date("Y-m-d", strtotime($date));  
                                $to_date =  date('Y-m-d', strtotime($date.' + 15 days'));
                                $days = [];
                                $days[]['date'] = $from_date;
                                do {
                                    $from_date = date("Y-m-d", strtotime("+1 day", strtotime($from_date)));
                                    $days[]['date'] = $from_date;
                                } while (strtotime($from_date) < strtotime($to_date)); 

                              // echo "<pre>"; print_r($leave_slot_array); exit;
                                return response()->json(['status' => 1, 'message' => 'Servicer Provider Available Days', 
                                    'details' => $days, 'data' => $days]);
                            /*}   else {
                                return response()->json(['status' => 0, 'message' => 'Invalid Service Provider']);
                            }
                            
                        }   else {
                            return response()->json(['status' => 0, 'message' => 'Invalid Service Provider']);
                        }*/
                        
                    }   else {
                        return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid Inputs']);
                    }
                }
            }    else {
                return response()->json([ 'status' => 0, 'data' => [], 'message' => $error]);
            }
        }   catch(\Throwable $th) {
            return response()->json(['status' => 0, 'data' => [], 'message' => $th->getMessage()]);
        } 
    }

    // Get the Current Bookings Service providers Leave Slots 

    public function getUserServicerAvailableSlotsPerDay(Request $request)    {
        try {   
            $inputJSON = file_get_contents('php://input');

            $input = json_decode($inputJSON, TRUE);

            $requiredParams = ['user_id', 'api_token', 'booking_id', 'schedule_date'];

            $error = $this->checkParams($input, $requiredParams, $request);

            if(empty($error)) {

                $userid = ((isset($input) && isset($input['user_id']))) ? $input['user_id'] : 0; 
                $booking_id = ((isset($input) && isset($input['booking_id']))) ? $input['booking_id'] : 0; 
                $schedule_date = isset($input["schedule_date"]) ? $input["schedule_date"] : '';
                
                $api_token = $request->header('x-api-key');
                $limit = CommonController::$page_limit;

                $mes = User::checkTokenExpiry($userid, $api_token);
                $status = $mes['status'];   $message = $mes['message'];
                if($status != 1) {
                    return response()->json([ 'status' => $status, 'data' => [], 'message' => $message]);
                }   else {
                    if($userid > 0) {
                        $date = strtotime(date('Y-m-d'));
                        $schdate = strtotime($schedule_date);

                        if($schdate < $date) {
                            return response()->json(['status' => 0, 'message' => 'Schedule Date must be the Future Date']);
                        }

                        $service_provider_id = Booking::where('id', $booking_id)->where('user_id', $userid)->value('service_provider_id');

                        $servicer_from_time = '';
                        if(date('Y-m-d', strtotime($schedule_date)) == date('Y-m-d')) {
                            $servicer_from_time = date("H:i");
                            $servicer_to_time = date("24:59");
                        }

                        $schedule_date = date('Y-m-d', strtotime($schedule_date));
                        $slots_qry = Slots::where('status', 'ACTIVE');
                        if($servicer_from_time != '') {
                            $slots_qry->where('from_time', '>=', $servicer_from_time)
                                ->where('to_time', '<=', $servicer_to_time);
                        }
                            
                        $slots = $slots_qry->orderby('position', 'ASC')->get();
                        $days = []; $leavedays = []; $ret_slots = [];
                        

                        if($service_provider_id>0) {
                            $servicers = ServiceProvider::where('user_id', $service_provider_id)->first();
                            
                            $servicer = DB::select('SELECT  em_servicer_leave_slots.*,
                                    GROUP_CONCAT(em_slots.slot_name ORDER BY em_slots.id) slotname
                                    FROM    em_servicer_leave_slots 
                                    INNER JOIN em_slots
                                        ON FIND_IN_SET(em_slots.id, em_servicer_leave_slots.leave_slots) > 0
                                    WHERE leave_date = "'.$schedule_date.'" 
                                    GROUP BY em_servicer_leave_slots.id');
                       
                            if(!empty($servicer)) {
                                foreach ($servicer as $key => $value) {
                                    $leavedays_str = $value->leave_slots;
                                    $leavedays = explode(',', $leavedays_str);
                                }
                            }
                        
                        }   

                        if($slots->isNotEmpty()) {
                            foreach ($slots as $key => $value) { 
                                if(in_array($value->id, $leavedays)) {
                                    unset($slots[$key]);
                                }
                                if($service_provider_id>0) {
                                    $servicer_bookings_count = Booking::where('service_provider_id', $service_provider_id)
                                        ->where('job_slot', $value->id)
                                        ->where('job_date', $schedule_date)
                                        ->whereNotIn('status', ['CANCELLED', 'ENDED'])->count();
                
                                    if($servicer_bookings_count >= $value->counts_per_slot) {
                                        unset($slots[$key]);
                                    }
                                }
                            }
                            
                            foreach ($slots as $key => $value) { 
                                $ret_slots[] = $value;
                            }
                        }
                       
                        return response()->json(['status' => 1, 'message' => 'Available Slot Details', 
                            'details' => $ret_slots]); 
                    }   else {
                        return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid Inputs']);
                    }
                }
            }    else {
                return response()->json([ 'status' => 0, 'data' => [], 'message' => $error]);
            }
        }   catch(\Throwable $th) {
            return response()->json(['status' => 0, 'data' => [], 'message' => $th->getMessage()]);
        }  

        if(!isset($input["user_id"]) || !isset($input["schedule_date"]) || !isset($input["booking_id"])) {
            return response()->json(['status' => 0, 'message' => 'Please input required parameters']);
        } 
    }


    public function getServicerLeaveSlots(Request $request)    {
        try {   
            $inputJSON = file_get_contents('php://input');

            $input = json_decode($inputJSON, TRUE);

            $requiredParams = ['user_id', 'api_token', 'leave_date', 'month', 'year'];

            $error = $this->checkParams($input, $requiredParams, $request);

            if(empty($error)) {
                

                $userid = ((isset($input) && isset($input['user_id']))) ? $input['user_id'] : 0; 
                $leave_date = ((isset($input) && isset($input['leave_date']))) ? $input['leave_date'] : ''; 
                $month = isset($input["month"]) ? $input["month"] : '';
                $year = isset($input["year"]) ? $input["year"] : '';
                
                $api_token = $request->header('x-api-key');

                $mes = User::checkTokenExpiry($userid, $api_token);
                $status = $mes['status'];   $message = $mes['message'];
                if($status != 1) {
                    return response()->json([ 'status' => $status, 'data' => [], 'message' => $message]);
                }   else {
                    if($userid > 0) {
                        $service_provider_id =  $userid;
                        $exuser = User::where('id', $service_provider_id)->first();
                        if(!empty($exuser)) {
                            $servicers = ServiceProvider::where('user_id', $service_provider_id)->first();
                            
                            if(!empty($servicers)) {
                            } else {
                                return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid Service Provider']);
                            }

                            $servicer_from_time = $servicer_to_time = '';
                            if(date('Y-m-d', strtotime($leave_date)) == date('Y-m-d')) {
                                $servicer_from_time = date("H:i");
                                $servicer_to_time = date("24:59");
                            }

                            
                            $slots = Slots::where('status', 'ACTIVE')
                                /*->where('from_time', '>=', $servicer_from_time)
                                ->where('to_time', '<=', $servicer_to_time)*/
                                ->orderby('position', 'ASC')->get();

                            $device_type = '';
                            $days = []; $leavedays = [];
                           // echo "<pre>"; print_r($slots); 
                            if($device_type == 'ANDROID') {
                                if(empty(trim($leave_date))) {
                                    $start_date = date("Y-m-d", strtotime($year.'-'.$month.'-01'));  
                                    $from_date = date("Y-m-d", strtotime($year.'-'.$month.'-01'));  
                                    $to_date =  date('Y-m-t', strtotime(date('M-Y', strtotime($from_date))));

                                    $days[] = $from_date;
                                    do {
                                        $from_date = date("Y-m-d", strtotime("+1 day", strtotime($from_date)));
                                        $days[] = $from_date;
                                    } while (strtotime($from_date) < strtotime($to_date)); 
                                    
                                    /*foreach($days as $k => $v) {
                                        $leavedays[$v] = ['slot_ids'=>'', 'slot_names'=>''];
                                    }*/

                                    $servicer = DB::select('SELECT  em_servicer_leave_slots.*,
                                                GROUP_CONCAT(em_slots.slot_name ORDER BY em_slots.id) slotname
                                        FROM    em_servicer_leave_slots 
                                                INNER JOIN em_slots
                                                    ON FIND_IN_SET(em_slots.id, em_servicer_leave_slots.leave_slots) > 0
                                                WHERE leave_date >= "'.$start_date.'" and leave_date <= "'.$to_date.'" 
                                                GROUP BY em_servicer_leave_slots.id');
                                        
                                    if(!empty($servicer)) {
                                        foreach ($servicer as $key => $value) {
                                           // if(isset($leavedays[$value->leave_date])) {
                                                $leavedays[$value->leave_date]['slot_ids'] = $value->leave_slots;
                                                $leavedays[$value->leave_date]['slot_names'] = $value->slotname;
                                          //  }
                                        }
                                    }
                                }   else {
                                    $leave_date = date('Y-m-d', strtotime($leave_date));
                                    /*$servicer = DB::table('bk_servicer_leave_slots')
                                        ->where('servicer_id', $service_provider_id)
                                        ->where('leave_date', $leave_date)
                                        ->first();*/

                                    $servicer = DB::select('SELECT  em_servicer_leave_slots.*,
                                                GROUP_CONCAT(em_slots.slot_name ORDER BY em_slots.id) slotname
                                        FROM    em_servicer_leave_slots 
                                                INNER JOIN em_slots
                                                    ON FIND_IN_SET(em_slots.id, em_servicer_leave_slots.leave_slots) > 0
                                                WHERE leave_date = "'.$leave_date.'" 
                                                GROUP BY em_servicer_leave_slots.id');

                                    if(!empty($servicer)) {
                                        foreach ($servicer as $key => $value) {
                                            $leavedays[$value->leave_date]['slot_ids'] = $value->leave_slots;
                                            $leavedays[$value->leave_date]['slot_names'] = $value->slotname;
                                        }
                                    }
                                }  
                            }   else {
                                $start_date = date("Y-m-d", strtotime($year.'-'.$month.'-01'));  
                                $from_date = date("Y-m-d", strtotime($year.'-'.$month.'-01'));  
                                $to_date =  date('Y-m-t', strtotime($from_date.' +2 month'));

                                $leavedays = [];

                                $servicer = DB::select('SELECT  em_servicer_leave_slots.*,
                                            GROUP_CONCAT(em_slots.slot_name ORDER BY em_slots.id) slotname
                                    FROM    em_servicer_leave_slots 
                                            INNER JOIN em_slots
                                                ON FIND_IN_SET(em_slots.id, em_servicer_leave_slots.leave_slots) > 0
                                            WHERE leave_date >= "'.$start_date.'" and leave_date <= "'.$to_date.'" 
                                            GROUP BY em_servicer_leave_slots.id');
                                    
                                if(!empty($servicer)) {
                                    foreach ($servicer as $key => $value) {
                                        //if(isset($leavedays[$value->leave_date])) {
                                            $leavedays[$value->leave_date]['slot_ids'] = $value->leave_slots;
                                            $leavedays[$value->leave_date]['slot_names'] = $value->slotname;
                                        //}
                                    }
                                }
                            }

                            $leave_slot_array = [];
                            if(count($leavedays)>0) {
                                foreach ($leavedays as $key => $value) { 
                                    $slotname = (!empty($value['slot_names'])) ? $value['slot_names'] : "0";
                                    $leave_slot_array[] = ['slot_ids'=>$value['slot_ids'], 
                                        'slot_names'=> $slotname, 
                                        'leave_date'=>$key];
                                }
                            }
                           //echo "<pre>"; print_r($servicer); exit;
                            return response()->json(['status' => 1, 'message' => 'Servicer Provider Leave Details', 
                                'details' => $leave_slot_array]);
                        }   else {
                            return response()->json(['status' => 0, 'message' => 'Invalid Service Provider']);
                        }
                    }   else {
                        return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid Inputs']);
                    }
                }
            }    else {
                return response()->json([ 'status' => 0, 'data' => [], 'message' => $error]);
            }
        }   catch(\Throwable $th) {
            return response()->json(['status' => 0, 'data' => [], 'message' => $th->getMessage()]);
        }  
    }
        
    /* Get Servicer Bookings */
    public function getServicerBookingPayments(Request $request) {
        try {   
            $inputJSON = file_get_contents('php://input');

            $input = json_decode($inputJSON, TRUE);

            $requiredParams = ['user_id', 'api_token'];

            $error = $this->checkParams($input, $requiredParams, $request);

            if(empty($error)) {

                $userid = ((isset($input) && isset($input['user_id']))) ? $input['user_id'] : 0; 
                $page_no = ((isset($input) && isset($input['page_no']))) ? $input['page_no'] : 0; 
                if(empty($page_no)) {
                    $page_no = 0;
                } 
                $api_token = $request->header('x-api-key');
                $limit = CommonController::$page_limit;

                $mes = User::checkTokenExpiry($userid, $api_token);
                $status = $mes['status'];   $message = $mes['message'];
                if($status != 1) {
                    return response()->json([ 'status' => $status, 'data' => [], 'message' => $message]);
                }   else {
                    if($userid > 0) {  

                        $bookings_qry = Booking::select('em_booking.id', 'ref_no', 'user_id', 'service_provider_id', 
                            'total_amount', 'payment_mode', 'payment_date', 'transaction_id', 
                            'rating', 'rating_comment', 'rated_date', 
                            DB::RAW('concat(ref_no, " payment received") as message')
                        )
                            ->where('em_booking.service_provider_id', $userid);

                        $orderby = 'em_booking.id'; $ordermode = 'DESC';
                        $bookings_qry->whereRaw("((`em_booking`.`status` in ('COMPLETED') and `payment_status` = 'PAID'))");
                        
                        $bookings =   $bookings_qry->orderby($orderby,$ordermode)
                            ->skip($page_no)->limit($limit)
                            ->get();

                        $provider_total_amount = $provider_total_earned = 0;

                        $total_payment = DB::select('SELECT sum(total_amount) as total_payment FROM `em_service_provider_payments` WHERE service_provider_id='.$userid);

                        if(!empty($total_payment) && count($total_payment)>0) {
                            $provider_total_amount = $total_payment[0]->total_payment;
                        }

                        if($provider_total_amount > 0) {}
                        else { $provider_total_amount = 0; }

                        $total_payout = DB::select('SELECT sum(total_amount) as total_earned FROM `em_service_provider_payments` WHERE service_provider_id='.$userid.' and provider_settlement="PAID"');
                        
                        if(!empty($total_payout) && count($total_payout)>0) {
                            $provider_total_earned = $total_payout[0]->total_earned;
                        }

                        if($provider_total_earned > 0) {}
                        else { $provider_total_earned = 0; }
                        
                        if($bookings->isNotEmpty()) {
                            return response()->json([ 'status' => 1, 'data' => $bookings, 'message' => 'Bookings List', 'provider_total_amount' => $provider_total_amount, 'provider_total_earned' => $provider_total_earned]);
                        }   else {
                            return response()->json([ 'status' => 0, 'data' => [], 'message' => 'No Bookings', 'provider_total_amount' => $provider_total_amount, 'provider_total_earned' => $provider_total_earned]);
                        } 
                    }   else {
                        return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid Inputs']);
                    }
                }
            }    else {
                return response()->json([ 'status' => 0, 'data' => [], 'message' => $error]);
            }
        }   catch(\Throwable $th) {
            return response()->json(['status' => 0, 'data' => [], 'message' => $th->getMessage()]);
        }  
    }


    /* Get Servicer Bookings Earned */
    public function getServicerBookingEarned(Request $request) {
        try {   
            $inputJSON = file_get_contents('php://input');

            $input = json_decode($inputJSON, TRUE);

            $requiredParams = ['user_id', 'api_token'];

            $error = $this->checkParams($input, $requiredParams, $request);

            if(empty($error)) {

                $userid = ((isset($input) && isset($input['user_id']))) ? $input['user_id'] : 0; 
                $page_no = ((isset($input) && isset($input['page_no']))) ? $input['page_no'] : 0; 
                if(empty($page_no)) {
                    $page_no = 0;
                } 
                $api_token = $request->header('x-api-key');
                $limit = CommonController::$page_limit;

                $mes = User::checkTokenExpiry($userid, $api_token);
                $status = $mes['status'];   $message = $mes['message'];
                if($status != 1) {
                    return response()->json([ 'status' => $status, 'data' => [], 'message' => $message]);
                }   else {
                    if($userid > 0) {  

                        $provider_total_amount = $provider_total_earned = 0;

                        $total_payment = DB::select('SELECT sum(total_amount) as total_payment FROM `em_service_provider_payments` WHERE service_provider_id='.$userid);

                        if(!empty($total_payment) && count($total_payment)>0) {
                            $provider_total_amount = $total_payment[0]->total_payment;
                        }

                        if($provider_total_amount > 0) {}
                        else { $provider_total_amount = 0; }

                        $total_payout = DB::select('SELECT sum(total_amount) as total_earned FROM `em_service_provider_payments` WHERE service_provider_id='.$userid.' and provider_settlement="PAID"');
                        
                        if(!empty($total_payout) && count($total_payout)>0) {
                            $provider_total_earned = $total_payout[0]->total_earned;
                        }

                        if($provider_total_earned > 0) {}
                        else { $provider_total_earned = 0; }

                        $bookings_qry = Booking::select('em_booking.id', 'ref_no', 'sub_total', 'em_booking.total_amount',
                            'code', 'commission_percentage', 'commission_amount', 'em_service_provider_payments.total_amount as servicer_amount', 'transaction_details', 'transaction_amount',  'mode', 'em_service_provider_payment_details.payment_date as payout_date', 'em_service_provider_payment_details.comments') 
                                ->leftjoin('em_service_provider_payments', 'em_service_provider_payments.booking_id', 'em_booking.id')
                                ->leftjoin('em_service_provider_payment_details', 'em_service_provider_payment_details.booking_id', 'em_service_provider_payments.booking_id')
                                ->where('em_booking.service_provider_id', $userid)->where('em_service_provider_payments.provider_settlement', 'PAID');
 

                        $orderby = 'em_booking.id'; $ordermode = 'DESC';
                        $bookings_qry->whereRaw("((`em_booking`.`status` in ('COMPLETED') and `payment_status` = 'PAID'))");
                        
                        $bookings =   $bookings_qry->orderby($orderby,$ordermode)
                            ->skip($page_no)->limit($limit)
                            ->get();
                        
                        if($bookings->isNotEmpty()) {
                            return response()->json([ 'status' => 1, 'data' => $bookings, 'message' => 'Bookings List', 'provider_total_amount' => $provider_total_amount, 'provider_total_earned' => $provider_total_earned]);
                        }   else {
                            return response()->json([ 'status' => 0, 'data' => [], 'message' => 'No Bookings', 'provider_total_amount' => $provider_total_amount, 'provider_total_earned' => $provider_total_earned]);
                        } 
                    }   else {
                        return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid Inputs']);
                    }
                }
            }    else {
                return response()->json([ 'status' => 0, 'data' => [], 'message' => $error]);
            }
        }   catch(\Throwable $th) {
            return response()->json(['status' => 0, 'data' => [], 'message' => $th->getMessage()]);
        }  
    }

    /*  FAQ  
    Fn Name: getFaq
    Input: user_id   
    return: Success Message saved / Failure Message
    */
    public function getUserFaq(Request $request)     {
        try {   
            $inputJSON = file_get_contents('php://input');

            $input = json_decode($inputJSON, TRUE);

            $requiredParams = ['user_id', 'api_token'];

            $error = $this->checkParams($input, $requiredParams, $request);

            if(empty($error)) {

                $userid = ((isset($input) && isset($input['user_id']))) ? $input['user_id'] : 0;
                $api_token = $request->header('x-api-key');

                $mes = User::checkTokenExpiry($userid, $api_token);
                $status = $mes['status'];   $message = $mes['message'];
                if($status != 1) {
                    return response()->json([ 'status' => $status, 'data' => [], 'message' => $message]);
                }   else {
                    if($userid > 0) { 

                        $helpcontact = DB::table('em_admin_settings')->where('id', 1)->value('helpcontact');

                        $faq = DB::table('em_faq')->where('status', 'ACTIVE')->where('faq_for', 'USER')->orderby('position', 'asc')->get();

                        if($faq->isNotEmpty()) {
                            return response()->json(['status' => 1, 'message' => 'FAQ', 'data' => $faq, 'helpcontact'=>$helpcontact]);
                        }   else {
                            return response()->json(['status' => 0, 'message' => 'No FAQ Detail', 'data' => [], 'helpcontact'=>$helpcontact]);
                        }
                    }   else {
                        return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid Inputs']);
                    }
                }
            }    else {
                return response()->json([ 'status' => 0, 'data' => [], 'message' => $error]);
            }
        }   catch(\Throwable $th) {
            return response()->json(['status' => 0, 'data' => [], 'message' => $th->getMessage()]);
        }  

    }

    public function getProviderFaq(Request $request)     {
        try {   
            $inputJSON = file_get_contents('php://input');

            $input = json_decode($inputJSON, TRUE);

            $requiredParams = ['user_id', 'api_token'];

            $error = $this->checkParams($input, $requiredParams, $request);

            if(empty($error)) {

                $userid = ((isset($input) && isset($input['user_id']))) ? $input['user_id'] : 0;
                $api_token = $request->header('x-api-key');

                $mes = User::checkTokenExpiry($userid, $api_token);
                $status = $mes['status'];   $message = $mes['message'];
                if($status != 1) {
                    return response()->json([ 'status' => $status, 'data' => [], 'message' => $message]);
                }   else {
                    if($userid > 0) { 

                        $helpcontact = DB::table('em_admin_settings')->where('id', 1)->value('helpcontact');

                        $faq = DB::table('em_faq')->where('status', 'ACTIVE')->where('faq_for', 'SERVICEPROVIDER')->orderby('position', 'asc')->get();

                        if($faq->isNotEmpty()) {
                            return response()->json(['status' => 1, 'message' => 'FAQ', 'data' => $faq, 'helpcontact'=>$helpcontact]);
                        }   else {
                            return response()->json(['status' => 0, 'message' => 'No FAQ Detail', 'data' => [], 'helpcontact'=>$helpcontact]);
                        }
                    }   else {
                        return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid Inputs']);
                    }
                }
            }    else {
                return response()->json([ 'status' => 0, 'data' => [], 'message' => $error]);
            }
        }   catch(\Throwable $th) {
            return response()->json(['status' => 0, 'data' => [], 'message' => $th->getMessage()]);
        }  

    }

    // Un Read Notifications 
    public function getNotifications(Request $request) {
        try {   
            $inputJSON = file_get_contents('php://input');

            $input = json_decode($inputJSON, TRUE);

            $requiredParams = ['user_id', 'api_token', 'page_no'];

            $error = $this->checkParams($input, $requiredParams, $request);

            if(empty($error)) {

                $userid = ((isset($input) && isset($input['user_id']))) ? $input['user_id'] : 0;
                $page_no = ((isset($input) && isset($input['page_no']))) ? $input['page_no'] : 0;

                if(empty($page_no)) {
                    $page_no = 0;
                }

                $api_token = $request->header('x-api-key');

                $mes = User::checkTokenExpiry($userid, $api_token);
                $status = $mes['status'];   $message = $mes['message'];
                if($status != 1) {
                    return response()->json([ 'status' => $status, 'data' => [], 'message' => $message]);
                }   else {
                    if($userid > 0) { 
                        $limit = CommonController::$page_limit;
         
                        $notifications = DB::table('em_notifications')->where('user_id', $userid)
                            ->where('read_status', 0)->orderby('notify_date', 'ASC')
                            ->skip($page_no)->take($limit)->get();

                        if($notifications->isNotEmpty()) {

                            foreach ($notifications as $key => $value) {
                                $ids[] = $value->id;
                            }
                            DB::table('em_notifications')->where('user_id', $userid)
                            ->whereNull('read_date')
                            ->whereIn('id', $ids)
                            ->update([
                                'read_date' => date('Y-m-d H:i:s'),
                                'read_status' => 1,
                                'updated_at' => date('Y-m-d H:i:s')
                            ]);

                            return response()->json(['status' => 1, 'message' => 'Notifications', 'details' => $notifications]);    
                        }   else {
                            return response()->json([ 'status' => 0, 'data' => [], 'message' => 'No Notifications']);    
                        }
                    }   else {
                        return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid Inputs']);
                    }
                }
            }    else {
                return response()->json([ 'status' => 0, 'data' => [], 'message' => $error]);
            }
        }   catch(\Throwable $th) {
            return response()->json(['status' => 0, 'data' => [], 'message' => $th->getMessage()]);
        } 
    }

    /* User Search level 2 and 3 categories and sub categories */
    public function getSearchCategories(Request $request)
    {

        try {   
            $inputJSON = file_get_contents('php://input');

            $input = json_decode($inputJSON, TRUE);

            $requiredParams = ['user_id', 'api_token', 'search'];

            $error = $this->checkParams($input, $requiredParams, $request);

            if(empty($error)) {

                $userid = ((isset($input) && isset($input['user_id']))) ? $input['user_id'] : 0; 
                $search = ((isset($input) && isset($input['search']))) ? $input['search'] : ''; 
                $page_no = ((isset($input) && isset($input['page_no']))) ? $input['page_no'] : 0;  
                $limit = CommonController::$page_limit;

                $api_token = $request->header('x-api-key');
                $limit = CommonController::$page_limit; 
                $mes = User::checkTokenExpiry($userid, $api_token);
                $status = $mes['status'];   $message = $mes['message'];
                if($status != 1) {
                    return response()->json([ 'status' => $status, 'data' => [], 'message' => $message]);
                }   else {
                    if($userid > 0) { 
                        if(empty(trim($search))) {
                            return response()->json(['status' => 0, 'message' => 'Invalid Search']);
                        }

                        $subcats = DB::table('em_sub_category')
                            ->where('status','ACTIVE')
                            ->where('name', 'like', '%'.$search.'%')
                            ->select('id', 'category_id', 'name', DB::Raw('"2" as level'), 'position');

                        $services = DB::table('em_sub_cat_services')
                            ->where('status','ACTIVE')
                            ->where('name', 'like', '%'.$search.'%')
                            ->select('id', 'sub_category_id as category_id', 'name', DB::Raw('"3" as level'), 'position');

            
                        $searchlist = $services->union($subcats)
                            ->orderby('name', 'asc')->skip($page_no)->take($limit)->get(); 

                        if(!empty($searchlist)) {
                            if($userid > 0) {
                                $language_id = User::where('id', $userid)->value('language_id');
                                if($language_id > 0) {
                                    foreach ($searchlist as $key => $value) {
                                        if($value->level == 2) {
                                            $lang = DB::table('em_category_language')
                                                ->where('language_id', $language_id)
                                                ->where('category_id', $value->id)
                                                ->where('level', 2)
                                                ->get();
                                            if($lang->isNotEmpty()) {
                                                $searchlist[$key]->name = (!empty(trim($lang[0]->title))) ? $lang[0]->title : $searchlist[$key]->name; 
                                            }
                                        }   else {
                                            $lang = DB::table('em_category_language')
                                                ->where('language_id', $language_id)
                                                ->where('category_id', $value->id)
                                                ->where('level', 3)
                                                ->get();
                                            if($lang->isNotEmpty()) {
                                                $categories[$key]->name = (!empty(trim($lang[0]->title))) ? $lang[0]->title : $searchlist[$key]->name; 
                                            }
                                        }                            
                                    }
                                }
                            }
                            return response()->json(['status' => 1, 'message' => 'Search', 'data' => $searchlist]);
                        }   else {
                            return response()->json(['status' => 0, 'message' => 'Search', 'data' => []]);
                        }    
                    }   else {
                        return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid Inputs']);
                    }
                }
            }    else {
                return response()->json([ 'status' => 0, 'data' => [], 'message' => $error]);
            }
        }   catch(\Throwable $th) {
            return response()->json(['status' => 0, 'data' => [], 'message' => $th->getMessage()]);
        }       
    }

    // Servicer Rate the Booking 
    public function postServicerRateBooking(Request $request)    {
        try {   
            $inputJSON = file_get_contents('php://input');

            $input = json_decode($inputJSON, TRUE);

            $requiredParams = ['user_id', 'api_token', 'booking_id', 'rate_value', 'rate_comments'];

            $error = $this->checkParams($input, $requiredParams, $request);

            if(empty($error)) {

                $userid = ((isset($input) && isset($input['user_id']))) ? $input['user_id'] : 0; 
                $booking_id = ((isset($input) && isset($input['booking_id']))) ? $input['booking_id'] : 0; 
                $rate_value = ((isset($input) && isset($input['rate_value']))) ? $input['rate_value'] : 0; 
                $rate_comments = ((isset($input) && isset($input['rate_comments']))) ? $input['rate_comments'] :''; 
                
                $api_token = $request->header('x-api-key');
                $limit = CommonController::$page_limit;

                $mes = User::checkTokenExpiry($userid, $api_token);
                $status = $mes['status'];   $message = $mes['message'];
                if($status != 1) {
                    return response()->json([ 'status' => $status, 'data' => [], 'message' => $message]);
                }   else {
                    if($userid > 0 && $booking_id > 0 && $rate_value > 0) {

                        Booking::where('service_provider_id', $userid)
                            ->where('id', $booking_id)
                            ->update(['provider_rating'=>$rate_value, 'provider_rating_comment'=>$rate_comments, 
                                'provider_rated_date' => date('Y-m-d H:i:s')]);
 
                        return response()->json(['status' => 1, 'message' => 'Booking Rating Updated']);
                    
                    }   else {
                        return response()->json([ 'status' => 0, 'data' => [], 'message' => 'Invalid Inputs']);
                    }
                }
            }    else {
                return response()->json([ 'status' => 0, 'data' => [], 'message' => $error]);
            }
        }   catch(\Throwable $th) {
            return response()->json(['status' => 0, 'data' => [], 'message' => $th->getMessage()]);
        }   
 
    }

}