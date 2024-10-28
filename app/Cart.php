<?php

namespace App;
use Illuminate\Database\Eloquent\Model;
use DB; 
use App\UserAddress;
use App\Slots;

class Cart extends Model
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
    protected $table = 'em_cart';

    protected $appends = ['user_address', 'slot_details'];
    
    public function cartItems(){

        return $this->hasMany('App\CartItem','cart_id','id')
            ->leftjoin('em_sub_service', 'em_sub_service.id', 'em_cart_subservices.sub_service_id')
            ->leftjoin('em_sub_category', 'em_sub_category.id', 'em_cart_subservices.sub_category_id')
            ->select('em_cart_subservices.*', 'em_cart_subservices.id as cartItemId', 'em_cart_subservices.sub_service_id as productId', 'em_sub_category.name as mainCategoryName',
             'em_cart_subservices.qty as count', 'em_cart_subservices.total_price as price', 'em_cart_subservices.price as itemprice',
             'em_sub_service.name as productTitle', 'em_sub_service.description as productDescription')
            ->orderby('em_cart_subservices.id', 'asc');
    }

    public function getUserAddressAttribute()    {
        $user_address_id = $this->user_address_id;
        $user_address = UserAddress::find($user_address_id);
        return $user_address;
    }

    public function getSlotDetailsAttribute()    {
        $slot_id = $this->slot_id;
        $slot_details = Slots::find($slot_id);
        return $slot_details;
    }

}