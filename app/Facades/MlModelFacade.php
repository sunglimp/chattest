<?php
namespace App\Facades;

use App\Libraries\MlModelAPI;
use Illuminate\Support\Facades\Facade;

class MlModelFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'MlModelAPI';
    }
}
