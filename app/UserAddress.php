<?php

namespace App;
use Illuminate\Database\Eloquent\Model;
use DB;

class UserAddress extends Model
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
    protected $table = 'users_address';
       
    protected $appends = ['zone_name'];

    public function getZoneNameAttribute(){

        $name = DB::table('em_zones')->where('id',$this->zone_id)->value('zone_name');

        if(!empty($name)){

            return $name;

        }else{

            return $name ='';
        }

    } 
}
