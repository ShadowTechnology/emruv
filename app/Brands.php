<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class Brands extends Model
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
    protected $table = 'em_brands';
    
    protected $appends = ['is_brand_image'];

    public function getIsBrandImageAttribute()
    {   
        return config("constants.APP_IMAGE_URL").'image/brands/'.$this->brand_image;
    }
    

}
