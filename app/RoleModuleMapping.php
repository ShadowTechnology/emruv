<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;
use App\Tabs;
class  RoleModuleMapping extends Model
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
    protected $table = 'em_role_access';
    protected $appends = [       
        'is_status'
      
        
    ];
    public function getIsStatusAttribute()
    {
        $email_ver = $this->role_status;
        if ($email_ver == 1) {
            $email_ver = "Active";
        } else if ($email_ver == 2) {
            $email_ver = "Inactive";
        }
        return $email_ver;
    }
    public function getIsRoleTypeAttribute()
    {
        $email_ver = $this->role_type;
        if ($email_ver == 1) {
            $email_ver = "Static";
        } else if ($email_ver == 2) {
            $email_ver = "Dynamic";
        }
        return $email_ver;
    }
}
