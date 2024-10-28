<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;
use App\Brands;

class Booking extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $table = 'em_booking';
     
    protected $appends = ['is_slot', 'is_user_address', 'service_provider', 'customer', 'is_reassign_available', 'created_before', 'is_rated', 'additional_instructions', 'additional_fees', 'servicers_images', 'users_images', 'is_ratecard', 'brand_detail'];

    public $products;

    public function bookItems(){

        $items = $this->hasMany('App\BookingSubServices','booking_id','id')
            ->leftjoin('em_sub_service', 'em_sub_service.id', 'em_booking_subservices.sub_service_id')
            ->leftjoin('em_sub_category', 'em_sub_category.id', 'em_booking_subservices.sub_category_id')
            ->select('em_booking_subservices.*', 'em_booking_subservices.id as cartItemId', 'em_booking_subservices.sub_service_id as productId', 'em_sub_category.name as mainCategoryName',
             'em_booking_subservices.qty as count', 'em_booking_subservices.amount as price', 'em_booking_subservices.price as itemprice',
             'em_sub_service.name as productTitle', 'em_sub_service.description as productDescription')
            ->orderby('em_booking_subservices.id', 'asc');

        
        return $items;
    }
 
    public function bookServices(){

        return $this->hasMany('App\BookingServices','booking_id','id');
    }

    public function getAdditionalFeesAttribute()    {
        return DB::table('em_booking_additional_fees')->where('booking_id',$this->id)->where('service_provider_id',$this->service_provider_id)->where('status','ACTIVE')->get();
    }

    public function getIsRatedAttribute()    {
        $rating = $this->rating;
        $is_rated = 0;
        if($rating > 0) {
            $is_rated = 1;
        }
        return $is_rated;
    }

    public function getAdditionalInstructionsAttribute()
    {
        $additional_instructions = '';
        if(!empty(trim($this->additional_instrn_ids))) {
            $instruction = DB::select('SELECT GROUP_CONCAT(instruction) as additional_instructions FROM `em_subcategory_instructions` where id in ('.$this->additional_instrn_ids.')');
            if(!empty($instruction)) {
                $additional_instructions = $instruction[0]->additional_instructions;
            }
        }         

        return $additional_instructions;
    }

    public function getIsSlotAttribute()
    {
        return DB::table('em_slots')->where('id',$this->job_slot)->first();
    }
    
    public function getBrandDetailAttribute()
    {
        return Brands::where('id',$this->brand_id)->first();
    }

    public function getIsUserAddressAttribute()
    {
        return UserAddress::where('id',$this->user_address_id)->first();
    }

    public function getServiceProviderAttribute()
    {
        return User::with('contactAddress')->where('id',$this->service_provider_id)->select('id', 'name', 'email', 'mobile', 'country')->first();
    }
    
    public function getCustomerAttribute()
    {
        return User::where('id',$this->user_id)->select('name', 'email', 'mobile', 'reg_no')->first();
    }

    public function getIsReassignAvailableAttribute() {
        $excount = DB::table('em_booking_cancelled')->leftjoin('em_booking', 'em_booking.id', 'em_booking_cancelled.id')->where('em_booking.id', $this->id)->count();
        return $excount;
    }

    public function getCreatedBeforeAttribute() {
        $timestamp_now = strtotime(date('Y-m-d H:i:s'));
        $created = strtotime($this->created_at);

        $minsadded = date('Y-m-d H:i:s', strtotime($this->created_at. ' +30 Minutes'));
        $minsadded_ts = strtotime($this->created_at. ' +30 Minutes');
        $difference1 = 0;  //echo $timestamp_now .'>'. $minsadded_ts.'='.date('Y-m-d H:i:s');
        if($timestamp_now > $minsadded_ts) {
            $diff = round(0,2);
            $difference = gmdate("H:i:s", $diff);
        }   else {
            $timestamp = strtotime($minsadded);
            $diff = round(abs($timestamp - $timestamp_now) / 60,2);
            $difference = gmdate("H:i:s", $diff);
        }
        
        return "30:00";
        //return date('Y-m-d H:i:s');
        /*return $difference;
        return $minsadded;*/
    }

    public function getServicersImagesAttribute() {
        $images = $this->servicer_images;
        $images_arr = [];$images_arr1 = [];
        if(!empty($images)) {
            $images_arr1 = explode(',', $images);
            foreach($images_arr1 as $k=>$v) {
                $images_arr[$k]['image'] = config("constants.APP_IMAGE_URL").'uploads/jobimages/'.$v;
            }
        }
        return $images_arr;
    }

    public function getUsersImagesAttribute() {
        $images = $this->user_images;
        $images_arr = [];$images_arr1 = [];
        if(!empty($images)) {
            $images_arr1 = explode(',', $images);
            foreach($images_arr1 as $k=>$v) {
                $images_arr[$k]['image'] = config("constants.APP_IMAGE_URL").'uploads/jobimages/'.$v;
            }
        }
        return $images_arr;
    }

    public function getIsRatecardAttribute(){
        $rate = '';  $ratecard = '';
        $sub_category_id = DB::table('em_booking_subservices')
            ->where('booking_id', $this->id)
            ->select('sub_category_id')
            ->groupby('booking_id')
            ->value('sub_category_id');
        if($sub_category_id > 0) {
            $ratecard = DB::table('em_sub_category')->where('id', $sub_category_id)->value('ratecard');
        }
        if(!empty($ratecard))
            return config("constants.APP_IMAGE_URL").'uploads/categories/'.$ratecard;
        else return $rate;
    }

}
