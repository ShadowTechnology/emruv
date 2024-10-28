<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class Banner extends Model
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
    protected $table = 'em_banner';
    
    protected $appends = ['is_image'];

    public function getIsImageAttribute()
    { 
        return config("constants.APP_IMAGE_URL").'image/banner/'.$this->banner_image;
    }
    

}
