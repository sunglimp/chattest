<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $dateFormat = 'U';
    public $timestamps = true;

    protected $fillable = [
        'chat_id','application_id','organization_id','ticket_data'
    ];
}
