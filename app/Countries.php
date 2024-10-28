<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class Countries extends Model
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
    protected $table = 'em_countries';
    
    protected $appends = ['is_country_flag'];

    public function getIsCountryFlagAttribute()
    {   
        return config("constants.APP_IMAGE_URL").'image/countries/'.$this->country_flag;
    }
    

}
