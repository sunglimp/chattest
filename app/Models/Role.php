<?php

namespace App\Models;

use App\User;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    public function role()
    {
        return $this->hasOne('App\User');
    }
    public function rolepermission()
    {
         return $this->hasOne('App\Models\OrganizationRolePermission', 'role_id');
    }
    
    public static function scopeUserRole($query, $id, $include_role=true)
    {
        $comperiosn= ($id==config('constants.user.role.super_admin')) ? ">" : (($include_role) ? ">=" : ">");

        return $query->where('id', $comperiosn, $id);
        
    }

    public function user()
    {
        return $this->hasmany(User::class, 'role_id', 'id');
    }
}
