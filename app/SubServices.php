<?php

namespace App;
use Illuminate\Database\Eloquent\Model;
use DB;

class SubServices extends Model
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
    protected $table = 'em_sub_service';
    
    protected $appends = ['is_image', 'is_qty', 'is_item_id', 'booking_sub_services'];

    public static $service_provider_id;

    public static $user_id;

    public static $booking_id;
    
    public function getIsImageAttribute()
    {
        return $this->getCategoryImage();
    }
    
    public function getCategoryImage(){

        return env('APP_IMAGE_URL').$this->image;
    }
    
    
    public function menuCategories(){
        
        return $this->hasMany('App\MenuCategory','main_category_id','id');
        
    }
    
    public function servicersdetails(){
        if(SubCategory::$service_provider_id > 0) { //'service_id','id'
            return $this->hasMany('App\SubServices','id','id')
               // ->leftjoin('em_servicer_service_details', 'em_servicer_service_details.sub_service_id', 'em_sub_service.id')
                ->where('em_sub_service.status', 'ACTIVE');
                /*->where('service_user_id', SubCategory::$service_provider_id);
                ->select('em_sub_service.*', 'em_servicer_service_details.service_type', 'em_servicer_service_details.hour_price', 'em_servicer_service_details.fixed_price');*/
        }   else {
            return $this->hasMany('App\SubServices','service_id','id')->where('status', 'ACTIVE');
        }
////echo SubServices::$service_provider_id."pp";
        /*return $this->hasMany('App\ServicerServiceDetails','sub_service_id','id')->where('status', 'ACTIVE')->where('service_user_id', SubCategory::$service_provider_id);*/
    }
    
    public function getIsQtyAttribute(){

        return DB::table('em_cart_subservices')->where('sub_service_id',$this->id)->where('user_id', self::$user_id)->sum('qty');
    }

    public function getIsItemIdAttribute(){

        $itemid = DB::table('em_cart_subservices')->where('sub_service_id',$this->id)->where('user_id', self::$user_id)->value('id');
        if(empty($itemid)) {
            $itemid = 0;
        }
        return $itemid;
    }

    public function getBookingSubServicesAttribute() {
        $subservices = '';
        if(self::$booking_id > 0) {
            $subservices = DB::table('em_booking_subservices')->where('booking_id', self::$booking_id)->where('sub_service_id',$this->id)->first();
        }  
        
        return $subservices;
    }
}
