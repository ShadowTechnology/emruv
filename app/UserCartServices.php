<?php

namespace App;
use Illuminate\Database\Eloquent\Model;
use DB;

class UserCartServices extends Model
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
    protected $table = 'em_cart_services';

    protected $appends = ['service_based_on', 'service_name', 'increment_value'];
    
    public function cartSubServices(){

        return $this->hasMany('App\UserCartSubServices','cart_service_id','id');
    }

    public function getServiceBasedOnAttribute()    {
        $service_based_on = DB::table('em_sub_cat_services')->where('id',$this->service_id)->value('service_based_on');
        return $service_based_on;
    }

    public function getServiceNameAttribute()    {
        $service_name = DB::table('em_sub_cat_services')->where('id',$this->service_id)->value('name');
        return $service_name;
    }

    public function getIncrementValueAttribute()    {
        $service_based_on = DB::table('em_sub_cat_services')->where('id',$this->service_id)->value('service_based_on');
        $increment_value = 1;
        if($service_based_on == 1) {
            // Hour based
            $hour_increment_by = DB::table('em_admin_settings')->where('id', 1)->value('hour_increment_by');

            if($hour_increment_by >= 0) {
                $increment_value = $hour_increment_by;
            }   else {
                $increment_value = 1;
            }
        }
        return $increment_value;
    }

}
