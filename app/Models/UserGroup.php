<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\User;

class UserGroup extends Model
{

    protected $dateFormat = 'U';
    public $timestamps = true;

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function group()
    {
        return $this->belongsTo('App\Models\Group');
    }
}
