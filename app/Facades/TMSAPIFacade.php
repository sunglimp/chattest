<?php
namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class TMSAPIFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'TMSAPI';
    }
}
