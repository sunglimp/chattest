<?php
namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class SurboAPIFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'SurboAPI';
    }
}