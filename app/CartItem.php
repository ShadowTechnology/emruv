<?php

namespace App;
use Illuminate\Database\Eloquent\Model;
use DB; 

class CartItem extends Model
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
    protected $table = 'em_cart_subservices';

}