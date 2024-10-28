<?php

namespace App;
use Illuminate\Database\Eloquent\Model;
use DB;
use App\Http\Controllers\CommonController;

class ServiceProvider extends Model
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
    protected $table = 'em_service_provider';
    
    protected $appends = ['id_proof_img', 'address_proof_img', 'registration_certificate_img', 'service_days', 'location_name', 'city_name', 'area_name', 'sel_categories', 'sel_sub_categories', 'sel_services', 'servicer_status', 'servicer_approve_status', 'rating', 'knownskills', 'knownlanguages', 'rating_info', 'is_profile_image', 'service_string', 'location_string'];  //, 'documents'

    public function getLocationStringAttribute() {
        $str = ''; 
        $provider = DB::table('users')->where('id',$this->user_id)->where('user_type', 'SERVICEPROVIDER')->first();
        if(!empty($provider)) { 
            $zone_ids = DB::table('em_service_provider')->where('user_id',$provider->id)->value('zone_ids');
            if(!empty($zone_ids)) {
                $zones = DB::select('SELECT GROUP_CONCAT(zone_name) as zones FROM `em_zones` where id in ('.$zone_ids.')');
                if(!empty($zones) && count($zones)>0) {
                    $str = $zones[0]->zones;
                }
            }
        } 
        return $str;
    }

    public function getServiceStringAttribute() {
        $str = '';  $stra = []; $sub_arr = $service_arr = [];  
        $provider = DB::table('users')->where('id',$this->user_id)->where('user_type', 'SERVICEPROVIDER')->first();
        if(!empty($provider)) {
            $sub_category_ids = DB::table('em_service_provider')->where('user_id',$provider->id)->value('sub_category_ids');
            if(!empty($sub_category_ids)) {
                SubCategory::$service_provider_id = $this->user_id;
                $subidarr = explode(',', $sub_category_ids);
                $subservices = SubCategory::with('services')
                    ->where('status', 'ACTIVE')
                    ->whereIn('id', $subidarr)
                    ->orderby('position', 'asc')->get();

                if($subservices->isNotEmpty()) { 
                    foreach($subservices as $service) { 
                        $sub_str = $service->name; 
                        if(!empty($service->services)) {    
                            $service_str = '';  $service_arr = [];
                            foreach($service->services as $service1) {
                                $service_arr[] = $service1->name; 
                            }
                            $service_str = implode(',', $service_arr);
                        }
                        if(!empty($service_str)) {
                            $sub_arr[] = $sub_str.' - '.$service_str;
                        }
                    }
                    
                }
            }
        }
        $str = implode(', ', $sub_arr);
        return $str;
    }

    public function getServicerServicesAttribute() {
        $str = '';  $stra = [];
        $provider = DB::table('users')->where('id',$this->user_id)->where('user_type', 'SERVICEPROVIDER')->first();
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

    public function getKnownLanguagesAttribute()
    {
        $languages = [];
        if(!empty($this->language_ids)) {
            $language_ids = explode(',', $this->language_ids);
            $languages = DB::table('em_known_language')->whereIn('id', $language_ids)->get();
        }
        
        return $languages;
    }

    public function getKnownSkillsAttribute()
    {
        $skills = [];
        if(!empty($this->skill_ids)) {
            $skill_ids = explode(',', $this->skill_ids);
            $skills = DB::table('em_skills')->whereIn('id', $skill_ids)->get();
        }
        
        return $skills;
    }

    public function getRatingAttribute() {
        $ratings = DB::select('SELECT avg(rating) as rating, count(id) as ratecount FROM em_booking where service_provider_id='.$this->user_id.' and rating>=0');
        if(!empty($ratings)) {
            $rating = $ratings[0]->rating;
            $ratecount = $ratings[0]->ratecount;
        }   else {
            $rating = 0;
            $ratecount = 0;
        }
        if($rating > 0) {} 
        else {$rating = 0; }
        return ['rating'=>$rating, 'ratecount'=>$ratecount];
    }

    public function getRatingInfoAttribute() {
        $rating = DB::table('em_booking')
            ->leftjoin('users', 'users.id', 'em_booking.user_id')
            ->where('service_provider_id',$this->user_id)->where('rating', '>=', 0)
            ->select('rating', 'rating_comment', 'rated_date', 'user_id', 'users.name')->get();
        if(!empty($rating)) {
        }   else {
            $rating = [];
        }
        return $rating;
    }
    
    public function getIdProofImgAttribute()    {
        $proofs = '';
        if(!empty($this->id_proof)) {
            $proofs = explode(';', $this->id_proof);
            if(count($proofs)>0) {
                foreach($proofs as $k=>$v) {
                    if(!empty($v)) {
                        $proofs[$k] = env('APP_IMAGE_URL').$v;
                    }   else {
                        unset($proofs[$k]);
                    }
                }
            }
            return $proofs;
        }
        else return '';
    }

    public function getAddressProofImgAttribute()    {
        $proofs = '';
        if(!empty($this->address_proof)) {
            $proofs = explode(';', $this->address_proof);
            if(count($proofs)>0) {
                foreach($proofs as $k=>$v) {
                    if(!empty($v)) {
                        $proofs[$k] = env('APP_IMAGE_URL').$v;
                    }   else {
                        unset($proofs[$k]);
                    }
                }
            }
            return $proofs;
        }
        else return '';
    }

    public function getRegistrationCertificateImgAttribute()    {
        $proofs = '';
        if(!empty($this->registration_certificate)) {
            $proofs = explode(';', $this->registration_certificate);
            if(count($proofs)>0) {
                foreach($proofs as $k=>$v) {
                    if(!empty($v)) {
                        $proofs[$k] = env('APP_IMAGE_URL').$v;
                    }   else {
                        unset($proofs[$k]);
                    }
                }
            }
            return $proofs;
        }
        else return '';
    }

    public function getIsProfileImageAttribute()    {
        if(!empty($this->profile_image))
            return env('APP_IMAGE_URL').$this->profile_image;
        else return env('APP_IMAGE_URL').'image/avatar.jpg';
    }

    public function getDocuments()    {
        $data = CommonController::getDocuments($this->user_id); 
        return $data;
    }
    
    public function getServiceDaysAttribute()
    {
        $service_days = '';
        $days = $this->working_days;
        if(!empty($days)) {
            $days = explode(',', $days);
            $service_days = DB::table('em_day_list')->select(DB::Raw('GROUP_CONCAT(day) as service_days'))->whereIN('id', $days)->first();
            if(!empty($service_days)) {
                $service_days = $service_days->service_days;
            }
        }
        
        return $service_days;
    }

    public function getLocationNameAttribute()
    {
        $location_name = '';
        //$location_name = DB::table('em_pinarea')->where('id', $this->location_id)->value('pinarea');
        return $location_name;
    }

    public function getCityNameAttribute()
    {
        $cityids = explode(',', $this->city_id);
        $city_name = DB::table('em_city_list')->whereIn('id', $cityids)
            ->select(DB::RAW('DISTINCT(GROUP_CONCAT(city)) as city'))->get();
        if($city_name->isNotEmpty()) {
            foreach ($city_name as $city) {
                $city_name = $city->city;
            }
        }   else {
            $city_name = '';
        }
        
        return $city_name;
    }

    public function getAreaNameAttribute()
    {   $area_name = '';
        /*$areaids = explode(',', $this->area_id);
        $area_name = DB::table('em_pinarea')->whereIn('id', $areaids)
            ->select(DB::RAW('DISTINCT(GROUP_CONCAT(pinarea)) as area'))->get();
        if($area_name->isNotEmpty()) {
            foreach ($area_name as $area) {
                $area_name = $area->area;
            }
        }   else {
            $area_name = '';
        }*/
        
        return $area_name;
        /*$area_name = DB::table('em_pinarea')->where('id', $this->area_id)->value('pinarea');
        return $area_name;*/
    }

    public function getSelCategoriesAttribute()
    {

        $sel_categories = DB::table('em_category')->where('id', $this->category_id)->value('name');
        return $sel_categories;
    }

    public function getSelSubCategoriesAttribute()
    {
        $sel_sub_catogories = '';
        $days = $this->sub_category_ids;
        if(!empty($days)) {
            $days = explode(',', $days);
            $sel_sub_catogories = DB::table('em_sub_category')->select(DB::Raw('GROUP_CONCAT(name) as sel_sub_catogories'))->whereIN('id', $days)->first();
            if(!empty($sel_sub_catogories)) {
                $sel_sub_catogories = $sel_sub_catogories->sel_sub_catogories;
            }
        }
        
        return $sel_sub_catogories;
    }

    public function getSelServicesAttribute()
    {
        $sel_services = '';
        $days = $this->service_ids;
        if(!empty($days)) {
            $days = explode(',', $days);
            $sel_services = DB::table('em_sub_cat_services')->select(DB::Raw('GROUP_CONCAT(name) as sel_services'))->whereIN('id', $days)->first();
            if(!empty($sel_services)) {
                $sel_services = $sel_services->sel_services;
            }
        }
        
        return $sel_services;
    }

    public function getServicerStatusAttribute()
    {
        $status = '';
        $status = DB::table('users')->where('id', $this->user_id)->value('status');
        
        return $status;
    }

    public function getServicerApproveStatusAttribute()
    {
        $approve_status = '';
        $approve_status = DB::table('users')->where('id', $this->user_id)->value('approve_status');
        
        return $approve_status;
    }
   
}
