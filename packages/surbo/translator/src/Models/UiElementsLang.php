<?php

namespace Surbo\Translator\Models;

use Illuminate\Database\Eloquent\Model;

class UiElementsLang extends Model
{
    protected $table = "ui_elements_lang";

    protected $fillable = ['organization_id','feature','key','value','locale','created_by'];

    /**
     * Scope function for feature
     *
     * @param [type] $query
     * @param [type] $feature
     * @return void
     */
    public function scopeOnlyFeature($query, $feature)
    {
        return $query->where('feature', $feature);
    }
}
