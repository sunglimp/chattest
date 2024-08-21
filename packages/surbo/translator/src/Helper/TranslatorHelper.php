<?php
namespace Surbo\Translator\Helper;

use Illuminate\Support\Facades\Lang;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use \App\Models\Organization;

class TranslatorHelper
{
    private $is_file = true;
    private $lang = 'en';
    
    /**
     * Function for get language file data
     *
     * @param string $fileName
     * @param string $languageType
     * @param string $folder
     * @param string $lang
     * @return array
     */
    public function getTranslatorData($fileName, $languageType='', $folder='default', $lang='en')
    {
        $data = '';
        $folder = ($folder!='') ? $folder : 'default';
        $path = $folder.'/'.$fileName;
        $path = ($languageType!='') ? $path.'.'.$languageType : $path;
        if ($this->is_file) {
            $data = $this->readLanguageFile($path, $lang);
        } else {
            $data = $this->readLanguageModel($path);
        }
        return $data;
    }
    
    /**
     * Read from database
     *
     * @return boolean
     * @todo when we use database we have to use this
     */
    private function readLanguageModel()
    {
        return false;
    }
    
    /**
     * Read language file
     *
     * @param string $path Language file path
     * @param string $lang Language abbreviation
     * @return array
     */
    private function readLanguageFile(string $path, string $lang)
    {
        return Lang::get($path, [], $lang);
    }
    
    /**
     * Function for get Organizational language data
     *
     * @param int $organizationId
     * @param string $languageFeature
     * @param string $languageType
     * @param mixed $languageSlug This optional, if it present this will return array of slug
     * @return array
     */
    public function getOrganizationLanguageData(int $organizationId, string $languageFeature, string $languageType, $languageSlug=false): array
    {
        try {
            $orgLanguages = $this->getOrgLanguages($organizationId);
            $folder = $organizationId;
            $defaultLanguageData = $this->getDefaultLanguageData($languageFeature, $languageType);
        
            foreach ($orgLanguages as $lang_abbr=>$lang) {
                $languageDetails[strtolower($lang)] = $this->getTranslatorData($languageFeature, $languageType, $folder, $lang_abbr);
            }
        
            $languageData = [];
        
            foreach ($defaultLanguageData as $key=>$data) {
                foreach ($orgLanguages as $lang_abbr => $language) {
                    $languageData[trim($key)]['default'] =  ['language_slug' => trim($key), 'language_data' => $data, 'lang_abbr' => $lang_abbr];
                    $languageData[trim($key)][strtolower($language)] =  ['language_slug' => trim($key), 'language_data' => $languageDetails[strtolower($language)][trim($key)] ?? '', 'language_abbr' => $lang_abbr];
                }
            }
            return ($languageSlug) ? $languageData[$languageSlug]: $languageData;
        } catch (\Exception $ex) {
            return [];
        }
    }
    
    /**
     * Function for get default language data
     *
     * @param string $languageFeature
     * @param string $languageType
     * @return type
     */
    public function getDefaultLanguageData(string $languageFeature, string $languageType='')
    {
        return $this->getTranslatorData($languageFeature, $languageType, '', $this->lang);
    }

    /**
     * Function for get organization configured language list
     *
     * @param int $organizationId
     * @return type
     */
    public function getOrgLanguages(int $organizationId)
    {
        return Organization::getLanguagesByOrgId($organizationId);
    }
}
