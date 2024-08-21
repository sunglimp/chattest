<?php
namespace App\Factory\permission;

class PermissionUpdateWrapper
{
    protected $data;
    protected $permission;


    public function __construct($data, $permission)
    {
        $this->data = $data;
        $this->permission = $permission;
    }
}
