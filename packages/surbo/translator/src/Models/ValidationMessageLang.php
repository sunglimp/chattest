<?php

namespace Surbo\Translator\Models;

use Illuminate\Database\Eloquent\Model;

class ValidationMessageLang extends Model
{
    protected $table = "validation_message_lang";

    protected $fillable = ['organization_id','feature','key','value','locale','created_by'];
}
