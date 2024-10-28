<?php

namespace App;
use Illuminate\Database\Eloquent\Model;
use DB;
use App\SubCategory;

class Category extends Model
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
    protected $table = 'em_category';
    
    protected $appends = ['is_image', 'sub_category_str', 'homeImage', 'mainCategories'];
    
    public static $subcats;

    public static $service_provider_id;

    public function getIsImageAttribute()
    {
        return $this->getCategoryImage();
    }
    
    public function getCategoryImage(){

        return config("constants.APP_IMAGE_URL").'uploads/categories/'.$this->image;
    }

    public function getHomeImageAttribute(){

        return config("constants.APP_IMAGE_URL").'uploads/categories/'.$this->image;
    }
    
    public function getMainCategoriesAttribute(){

        return SubCategory::where('category_id', $this->id)
            //->select('id as mainCategoryId', 'name as mainName', 'image', 'category_id')
            ->where('status', 'ACTIVE')->orderby('position', 'asc')->get();
    }
    
    public function subCategories(){
        
        return $this->hasMany('App\SubCategory','category_id','id')->where('status', 'ACTIVE')->orderby('position', 'asc');
        
    }

    public function getSubCategoryStrAttribute(){
        $sub_category = []; $sub_category_str = '';
        /*$subcat = DB::select('SELECT GROUP_CONCAT(name) as sub_category_str FROM `em_sub_category` order by id asc limit 0,3');*/

        $subcat_qry = DB::table('em_sub_category')->where('category_id', $this->id);
        if(is_array(self::$subcats) && count(self::$subcats)>0) {
            $subcat_qry->whereIn('id', self::$subcats);
        }
        $subcat  =  $subcat_qry->select('name')
            ->orderby('id','ASC')->skip(0)->take(3)->get(); 

        if($subcat->isNotEmpty()) {
            foreach ($subcat as $key => $value) {
                $sub_category[] = $value->name;
            }            
        }
        if(count($sub_category)>0) {
            $sub_category_str = implode(' | ', $sub_category);
        }
        return $sub_category_str;
        
    }
    
    
}
