<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class BookingSubServices extends Model
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
    protected $table = 'em_booking_subservices';

    protected $appends = ['sub_service_name'];

    public function getSubServiceNameAttribute()    {
        $sub_service_name = DB::table('em_sub_service')->where('id',$this->sub_service_id)->value('name');
        return $sub_service_name;
    }

}
