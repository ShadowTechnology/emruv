<?php

namespace App;
use Illuminate\Database\Eloquent\Model;

class SubCategory extends Model
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
    protected $table = 'em_sub_category';
    
    protected $appends = ['is_image', 'is_ratecard', 'is_video_token'];

    public static $service_provider_id;

    public function instructions(){

        return $this->hasMany('App\ServiceInstructions','sub_category_id','id');
    }
    
    public function getIsImageAttribute()
    {
        return $this->getCategoryImage();
    }
    
    public function getCategoryImage(){

        return config("constants.APP_IMAGE_URL").'uploads/categories/'.$this->image;
    }

    public function getMainImageAttribute(){

        return config("constants.APP_IMAGE_URL").'uploads/categories/'.$this->image;
    }

    public function getIsRatecardAttribute(){
        $rate = '';
        if(!empty($this->ratecard))
            return config("constants.APP_IMAGE_URL").'uploads/categories/'.$this->ratecard;
        else return $rate;
    }
    
    
    public function services(){
        
        if(SubCategory::$service_provider_id > 0) {
            /*return $this->hasMany('App\Services','sub_category_id','id')
                ->leftjoin('em_service_provider', \DB::raw("FIND_IN_SET(em_sub_cat_services.id, em_service_provider.service_ids)"),">",\DB::raw("'0'"))
                ->where('status', 'ACTIVE')
                ->where('user_id', SubCategory::$service_provider_id)->select('em_sub_cat_services.*');*/

            return $this->hasMany('App\Services','sub_category_id','id')
                ->leftjoin('em_service_provider', \DB::raw("FIND_IN_SET(em_sub_cat_services.id, em_service_provider.service_ids)"),">",\DB::raw("'0'"))->select('em_sub_cat_services.*')
                ->where('status', 'ACTIVE')
                ->where('user_id', SubCategory::$service_provider_id)->select('em_sub_cat_services.*')->orderby('position', 'asc');

        }   else {
                return $this->hasMany('App\Services','sub_category_id','id')->where('status', 'ACTIVE')->orderby('position', 'asc');
        }

       // return $this->hasMany('App\Services','sub_category_id','id')->where('status', 'ACTIVE');
    }
    
    
    public function subCategoryItems(){
        
        return $this->hasMany('App\Services','sub_category_id','id')->where('status', 'ACTIVE')
            //->select('id', 'sub_category_id as subCategoryId', 'name as subCategoryName', 'image', 'status', 'created_at')
            ->orderby('position', 'asc');    
    }

    public function getIsVideoTokenAttribute() {
        if(!empty($this->video_link)) {
            $arr = explode('/', $this->video_link);
            $last = end($arr);
            $arr1 = explode('?', $last);
            return current($arr1);
        }   else {
            return '';
        }
    }
}
