<?php

namespace Surbo\Translator;

use App\Models\Organization;
use function GuzzleHttp\json_decode;
use function Opis\Closure\serialize;
use Surbo\Translator\Models\UiElementsLang;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Surbo\Translator\Models\ValidationMessageLang;
use Surbo\Translator\Models\FailureMessageLang;
use Surbo\Translator\Models\SuccessMessageLang;

class OrganizationPackageObserver
{
    public function saved(Organization $organization)
    {
        $this->createLangFolder($organization);
    }
    
    /**
     * Function to create folder for organization.
     *
     * @param Model $organization
     */
    private function createLangFolder($organization)
    {
        $languages = json_decode($organization->languages, true);
        //for each language, create folder in language wise
        foreach ($languages as $lang) {
            $dirPath = __DIR__.'/../../../../resources/lang/'.$lang.'/'.$organization->id;
            if (!file_exists($dirPath)) {
                mkdir($dirPath, 0777, true);
            }
            //feature contrains feature of application and keys contain array odf keys
            $this->createLangFiles(config('messages-keys'), $organization, $lang);
        }
    }
    
    /**
     * Function to create feature wise files.
     *
     * @param array $elements
     * @param Model $organization
     * @param string $language
     */
    private function createLangFiles($messageKeys, $organization, $language)
    {
        foreach ($messageKeys as $featureKey=>$feature) {
            $this->createFile($language, $organization, $featureKey, $feature);
            //$this->insertLangRecords($feature, $organization, $language, $featureKey);
        }
    }
    
    /**
     * Function to create file in resource lang folder.
     *
     * @param string $language
     * @param Model $organization
     * @param string $featureKey
     * @param array $feature
     */
    private function createFile($language, $organization, $featureKey, $feature)
    {
        $fileName = __DIR__.'/../../../../resources/lang/'.$language.'/'.$organization->id.'/'.$featureKey.'.php';
             
        if (!file_exists($fileName)) {
            $default_values = __('default/'.$featureKey, [], $language);
            if (is_array($default_values)) {
                $feature = array_merge($feature, $default_values);
            }
            $value = '<?php return '.var_export($feature, true) .'?>';
        
            file_put_contents($fileName, $value);
            chmod($fileName, 0777);
        }
    }
    
    /**
     * Function to insert records in database.
     *
     */
    private function insertLangRecords($feature, $organization, $language, $featureKey)
    {
        foreach ($feature as $categoryKey=>$category) {
            if ($categoryKey == 'success_messages') {
                $model = SuccessMessageLang::class;
            } elseif ($categoryKey == 'fail_messages') {
                $model = FailureMessageLang::class;
            } elseif ($categoryKey == 'validation_messages') {
                $model = ValidationMessageLang::class;
            } elseif ($categoryKey == 'ui_elements_messages') {
                $model = UiElementsLang::class;
            }
            
            foreach ($category as $key=>$val) {
                $arr = [
                    'organization_id' => $organization->id,
                    'feature' => $featureKey,
                    'key'     => $key,
                    'value'   => '',
                    'locale'   => $language,
                    'created_by' => Auth::id() ?? null
                ];
                $model::create($arr);
            }
        }
    }
}
