<?php

namespace App;
use Illuminate\Database\Eloquent\Model;
use DB;

class ProviderPaymentDetails extends Model
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
    protected $table = 'em_service_provider_payment_details';

    protected $appends = ['is_provider_name'];

    public function getIsProviderNameAttribute()
    {
        return $this->getProviderName();
    }

    public function getProviderName(){

        $name = DB::table('users')->where('id',$this->service_provider_id)->value('name');

        if(!empty($name)){

            return $name;

        }else{

            return $name ='';
        }

    }
    
  
}
