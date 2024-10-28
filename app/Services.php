<?php

namespace App;
use Illuminate\Database\Eloquent\Model;
use DB; 

class Services extends Model
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
    protected $table = 'em_sub_cat_services';
    
    protected $appends = ['is_image', 'is_qty', 'is_item_id'];
    
    public static $service_provider_id;

    public static $user_id;


    public function getIsImageAttribute()
    {
        return $this->getCategoryImage();
    }
    
    public function getCategoryImage(){

        return config("constants.APP_IMAGE_URL").'uploads/services/'.$this->image;
    }
    
    
    public function subServices(){
        /*if(SubCategory::$service_provider_id > 0) {
            return $this->hasMany('App\SubServices','service_id','id')
                ->leftjoin('bk_servicer_service_details', 'bk_servicer_service_details.sub_service_id', 'bk_sub_service.id')
                ->where('bk_sub_service.status', 'ACTIVE')->where('service_user_id', SubCategory::$service_provider_id)->select('bk_sub_service.*');
        }   else {
            return $this->hasMany('App\SubServices','service_id','id')->where('status', 'ACTIVE');
        }*/

        return $this->hasMany('App\SubServices','service_id','id')->where('status', 'ACTIVE')->orderby('position', 'asc');
    }

    public function products(){

        return $this->hasMany('App\SubServices','service_id','id')->where('status', 'ACTIVE')->orderby('position', 'asc');
    }

    public function getIsQtyAttribute(){

        return DB::table('em_cart_services')->where('service_id',$this->id)->where('user_id', self::$user_id)->sum('qty');
    }

    public function getIsItemIdAttribute(){

        $itemid = DB::table('em_cart_services')->where('service_id',$this->id)->where('user_id', self::$user_id)->value('id');
        if(empty($itemid)) {
            $itemid = 0;
        }
        return $itemid;
    }
    
    
}
