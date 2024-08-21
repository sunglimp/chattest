<?php
use Illuminate\Support\Facades\Route;

$config = $this->app['config']->get('translator.route', []);
$config['namespace'] = 'Surbo\Translator';

Route::group($config, function ($route) {
    $route->get('/get', 'TranslatorController@get')->name('translator.index');
    $route->get('/get-lan-data', 'TranslatorController@getLanguageData')->name('translator.language-data');
    $route->post('/update-keys', 'TranslatorController@updateKeys')->name('translator.keys');
    $route->get('/get-translator-data', 'TranslatorController@getTranslatorData')->name('translator.get-data');
    $route->get('/get-org-languages', 'TranslatorController@getOrganizationLanguageList')->name('translator.get-org-languages');
});
