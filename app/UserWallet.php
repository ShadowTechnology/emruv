<?php

namespace App;
use Illuminate\Database\Eloquent\Model;
use DB;

class UserWallet extends Model
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
    protected $table = 'em_user_wallets';
    

    public function wallet_details() {
        return $this->hasMany('App\UserWalletDetail','wallet_id','id');
    }
}
