<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    public function adminpermission()
    {
         return $this->hasOne('App\Models\OrganizationRolePermission', 'permission_id');
    }
}

