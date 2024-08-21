<?php

namespace Surbo\Translator;

use App\Models\Organization;
use Surbo\Translator\Models\FailureMessageLang;
use Surbo\Translator\Models\SuccessMessageLang;
use Surbo\Translator\Models\UiElementsLang;
use Surbo\Translator\Models\ValidationMessageLang;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Foundation\Application;
use Validator;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Lang;
use Surbo\Translator\Helper\TranslatorHelper;

class Translator
{
    protected $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function getModel($type)
    {
        switch ($type) {
            case "ui-element":
                    $ModelClass = UiElementsLang::class;
                    break;
            case "success":
                    $ModelClass = SuccessMessageLang::class;
                    break;
            case "failure":
                    $ModelClass = FailureMessageLang::class;
                    break;
            case "validation":
                    $ModelClass = ValidationMessageLang::class;
                    break;
            default:
                     $ModelClass = UiElementsLang::class;
            }
        return $ModelClass;
    }

    /**
     * Insert new KEY into locale database
     *
     * @param string $type ui-element,success,failure,validation
     * @param array $data
     * @return void
     *
     *  data ['organization_id', 'feature','key','value','locale']
     */
    public function addNewKey($type = "ui-element", $data)
    {
        // $modelClass = $this->getModel($type);
        // $validator = Validator::make(
        //     ['key' => $data['key'], 'feature' => $data['feature']],
        //     ['key' => 'unique:' .(new $modelClass)->getTable() . ',key,NULL,id,feature,' . $data['feature'],
        //       ]
        // );
        // if ($validator->fails()) {
        //     $response['status'] = false;
        //     foreach ($validator->errors()->all() as $error) {
        //         $response['errors'][] = $error;
        //     }
        //     return $response;
        // }
        // if (! is_null($data['default_value'])) {
        //     self::updateDefaultKeyValue($data['feature'], $type, $data['key'], $data['default_value']);
        // }
        try {
            $organization_list = Organization::active()->select(['id', 'languages'])->get();
            foreach ($organization_list as $organization) {
                $org_lang = json_decode($organization->languages, true);
                if (in_array($data['locale'], $org_lang)) {
                    $language = [ $data['key'] => [
                    $data['locale'] => $data['value']
                 ] ];
               
                    Log::info("**********************");
                    Log::info($language);
                    self::updateKeys($organization->id, $data['feature'], $type, $language);
                    //   $modelClass::create($locale_data);
                }
            }
        } catch (\Exception $exception) {
            Log::error($exception->getMessage());
            $response['status']   = false;
            $response['errors'][] = 'Something went wrong';
            return $response;
        }
        return $response = ['status' => true];
    }

    /**
     * Update keys in respective feature file
     *
     * @param int $organizationId
     * @param string $feature
     * @param string $type
     * @param array $languages
     *               "languages" => [
     *                  "days": [
     *                       "ar": "dayyssss",
     *                       "fr": "dayyss"
     *                   ]
     *               ]
     * @return void
     */
    public static function updateKeys($organizationId, $feature, $type, $languages)
    {
        foreach ($languages as $key => $value) {
            foreach ($value as $language => $val) {
                App::setLocale($language);
                $fileName              = __DIR__ . '/../../../../resources/lang/' . $language . '/' . $organizationId . '/' . $feature . '.php';
                $messages              = (__($organizationId . '/' . $feature));
                if (isset($messages[$type])) {
                    $messages[$type][$key] = $val;
                }
                $output                = "<?php\n\nreturn " . var_export($messages, true) . ";\n";

                file_put_contents($fileName, $output);
            }
        }
    }

    public static function updateDefaultKeyValue($feature, $type, $key, $value)
    {
        $fileName = __DIR__ .'/../../../../resources/lang/en/default/' . $feature .'.php';
        $all_default =  (new TranslatorHelper)->getDefaultLanguageData($feature);
        // Log::info($all_default);
        if (isset($all_default[$type])) {
            $all_default[$type][$key] = $value;
        }
        $output                = "<?php\n\nreturn " . var_export($all_default, true) . ";\n";
        file_put_contents($fileName, $output);
    }
}
