<?php

namespace App;

use App\Http\Controllers\CommonController;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use DB;
use App\Countries;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];


    protected $appends = ['is_profile_image', 'country', 'selected_address', 'servicer_services', 'bank_name', 'documents', 'rating'];

    public function getIsProfileImageAttribute()    {   
        if(!empty($this->profile_image))
            return config("constants.APP_IMAGE_URL").'uploads/documents/'.$this->profile_image;
        else return '';
    }
    
    public function getCountryAttribute() {  
        return Countries::where('phonecode',$this->country_code)->first();
    }

    public function getSelectedAddressAttribute() {  
        return DB::table('users_address')->where('user_id',$this->id)->where('is_default', 1)->first();
    }

    public function getBankNameAttribute() {  
        if($this->bank_id>0) {
            return DB::table('em_banks')->where('id',$this->bank_id)->where('status', 'ACTIVE')->value('bank_name');
        }   else {
            return '';
        }
    }

    public function getDocumentsAttribute()    {
        $data = CommonController::getDocuments($this->id); 
        return $data;
    }

    /*  Randomly generated string with the specific length
    Fn Name: random_strings
    return: Random String by default 5 characters of length
    */
    public static function random_strings($length_of_string=0) { 
        if($length_of_string == 0) $length_of_string = 5;
        $str_result = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890abcdefghijklmnopqrstuvwxyz'; 
        return substr(str_shuffle($str_result), 0, $length_of_string); 
    } 

    /*  Check the API Token Expiry for the User
    Fn Name: checkTokenExpiry
    return: true / false
    */
    public static function checkTokenExpiry($userid, $api_token) {
        $user = User::where('id', $userid)->where('api_token', $api_token)->limit(1)->get();
        if($user->isNotEmpty()) {
            if(isset($user[0])) {
                $user = $user[0];
                $expiry_date = $user->api_token_expiry;
                $date = date('Y-m-d H:i:s');

                $userstatus = $user->status;

                if(strtotime($expiry_date) <= strtotime($date)) {
                    if($user->user_role == 'GUESTUSER') {
                        $def_expiry_after =  CommonController::getDefExpiry();
                        $user->api_token_expiry = date('Y-m-d H:i:s', strtotime('+'.$def_expiry_after.' months'. $date));
                        $user->save(); 
                        return array('status' => 1, 'message' => 'Success');                   
                    }
                    //return array('status' => 8, 'message' => 'Token Expired');
                    $def_expiry_after =  CommonController::getDefExpiry();
                    $user->api_token_expiry = date('Y-m-d H:i:s', strtotime('+'.$def_expiry_after.' months'. $date));
                    $user->save(); 
                    return array('status' => 1, 'message' => 'Success');
                }   else { 
                    if($userstatus == 'REJECTED') {
                        return array('status' => 4, 'message' => 'Account Rejected by Admin');
                    }
                    if($userstatus == 'INACTIVE') {
                        return array('status' => 5, 'message' => 'Account is In-Activated by Admin');
                    }
                    $def_expiry_after =  CommonController::getDefExpiry();
                    $user->api_token_expiry = date('Y-m-d H:i:s', strtotime('+'.$def_expiry_after.' months'. $date));
                    $user->save(); 
                    return array('status' => 1, 'message' => 'Success');
                }
            }   else {
                return array('status' => 0, 'message' => 'Invalid Details');
            }
        }   else {
            return array('status' => 3, 'message' => 'Invalid User / Token / Device Changed. Logout and Login Again');
        }
    }                
            
    public function contactAddress(){

        return $this->hasMany('App\UserAddress','user_id','id')->where('is_default', 1);
    } 

    public function getServicerServicesAttribute() {
        $str = '';  $stra = [];
        $provider = DB::table('users')->where('id',$this->id)->where('user_type', 'SERVICEPROVIDER')->first();
        if(!empty($provider)) {
            $subcats = DB::table('em_service_provider')->where('user_id',$provider->id)->first();
            $subcatids = $subcats->category_id;
            $subs = $subcats->sub_category_ids;
            if(!empty($subcatids)) {
                $sub_arr = [];
                $subcat_arr = explode(',', $subcatids);
                if(!empty($subs)) {
                    $sub_arr = explode(',', $subs);
                }
                Category::$subcats = $sub_arr;
                $sub_cats = Category::whereIn('id', $subcat_arr)->get();
                if($sub_cats->isNotEmpty()) {
                    $arr = $sub_cats->toArray();
                    foreach($arr as $ar) {
                        $str = $ar['name'];
                        if(!empty($ar['sub_category_str']))
                            $str .= '('.$ar['sub_category_str'].')';
                        $stra[] = $str;
                    }
                }
            }
        }
        $str = implode('; ', $stra);
        return $str;
    }

    public function getRatingAttribute() {
        $rating = $ratecount = 0;

        $provider = DB::table('users')->where('id',$this->id)->where('user_type', 'SERVICEPROVIDER')->first();
        if(!empty($provider)) {
            $ratings = DB::select('SELECT avg(rating) as rating, count(id) as ratecount FROM em_booking where service_provider_id='.$this->id.' and rating>0');
            if(!empty($ratings)) {
                $rating = $ratings[0]->rating;
                $ratecount = $ratings[0]->ratecount;
            }   else {
                $rating = 0;
                $ratecount = 0;
            }
            if($rating > 0) {} 
            else {$rating = 0; }
        }
        
        return ['rating'=>$rating, 'ratecount'=>$ratecount];
    }
}
