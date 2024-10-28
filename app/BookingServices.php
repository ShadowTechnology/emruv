<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class BookingServices extends Model
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
    protected $table = 'em_booking_services';

    protected $appends = ['service_based_on', 'service_name'];

    public static $booking_id;
    
    public function bookSubServices(){

        return $this->hasMany('App\BookingSubServices','booking_service_id','id');
    }

    public function bookAdditionalSubServices(){
        return $this->hasMany('App\SubServices','service_id','service_id')
            ->where('status', 'ACTIVE')->orderby('position', 'asc');
        //return $this->hasMany('App\BookingSubServices','booking_service_id','id');
    }

    public function getServiceBasedOnAttribute()    {
        $service_based_on = DB::table('em_sub_cat_services')->where('id',$this->service_id)->value('service_based_on');
        return $service_based_on;
    }

    public function getServiceNameAttribute()    {
        $service_name = DB::table('em_sub_cat_services')->where('id',$this->service_id)->value('name');
        return $service_name;
    }
}
