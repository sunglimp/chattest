<?php

namespace Surbo\Translator\Models;

use Illuminate\Database\Eloquent\Model;

class SuccessMessageLang extends Model
{
    protected $table = "success_message_lang";

    protected $fillable = ['organization_id','feature','key','value','locale','created_by'];
}
