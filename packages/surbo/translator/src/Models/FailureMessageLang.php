<?php

namespace Surbo\Translator\Models;

use Illuminate\Database\Eloquent\Model;

class FailureMessageLang extends Model
{
    protected $table = "failure_message_lang";

    protected $fillable = ['organization_id','feature','key','value','locale','created_by'];
}
