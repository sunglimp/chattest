<?php
namespace App\Factory;

use App\Factory\permission\PermissionViewWrapper;
use App\Factory\permission\PermissionUpdateWrapper;

class PermissionFactory
{
    public static function view($request)
    {
        return new PermissionViewWrapper($request);
    }

    public static function update($request, $permission)
    {

        return new PermissionUpdateWrapper($request, $permission);
    }
}
