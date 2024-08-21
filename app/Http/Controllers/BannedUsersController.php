<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BannedUsersController extends BaseController
{
    public function __construct()
    {
        $this->middleware('can:all-admin');
    }
    
    protected $titles = [
        'banned-users' => 'Banned Users'
    ];
    
    public  function list()
    {
        return $this->getAngularView('banned-users');
    }
}
