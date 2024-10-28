<?php

namespace App;
use Illuminate\Database\Eloquent\Model;

class Slots extends Model
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
    protected $table = 'em_slots';
    
    public static $service_provider_id;
}
