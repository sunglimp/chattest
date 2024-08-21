<?php

namespace Surbo\Translator;

use App\Models\Organization;
use Illuminate\Routing\Controller;

use Surbo\Translator\Models\UiElementsLang;
use Surbo\Translator\Models\SuccessMessageLang;
use Surbo\Translator\Models\FailureMessageLang;
use Surbo\Translator\Models\ValidationMessageLang;
use Yajra\DataTables\DataTables;
use Surbo\Translator\Helper\TranslatorHelper;

use GuzzleHttp\json_decode;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Gate;
use Auth;
use Illuminate\Http\Request;
use Surbo\Translator\Translator;
use Illuminate\Support\Facades\Lang;


class TranslatorController extends Controller
{
    public function get()
    {
        $organization = Organization::active()->orderBy('created_at', 'desc')->get();
//         $languageJson = null;
//         if (isset($organization->languages)) {
//             $languageJson = $organization->languages ?? null;
//         }
//         $languageJson = $organization->languages ?? null;
//         dd($languageJson);
//         $languages = [];
//         if (!empty($languageJson)) {
//             $languages = json_decode($languageJson, true);
//         }
        $features = config('translator.features');
        $types = config('translator.type');
        return view('translator::index', ['organization' => $organization, 'features' => $features,'types'=>$types]);
    }
    

    public function getLanguageData(Request $request)
    {
        $languageType = $request->language_type;
        $languageFeature = $languageFeature = $request->language_feature;
        $organizationId = $request->organization_id;
        
        $orgLanguages = (new TranslatorHelper)->getOrgLanguages($organizationId);
        $language_names = array_map('strtolower', array_values($orgLanguages));
        
        
        $languageData = (new TranslatorHelper)->getOrganizationLanguageData($organizationId, $languageFeature, $languageType);
        
        $dataTable = Datatables::of($languageData);
        $dataTable->addColumn('default', function ($data) {
            return view('translator::datatables.default', [
                'data' => $data['default']
            ]);
        });
        
        foreach ($orgLanguages as $lang) {
            $lang_name = strtolower($lang);
            $dataTable->addColumn($lang_name, function ($data) use ($lang_name) {
                return view('translator::datatables.default', [
                    'data' => $data[$lang_name]
                ]);
            });
        }
        
        $dataTable->addColumn('action', function ($data) use ($languageType, $languageFeature) {
            return view('translator::datatables.action', [
                'languageType' => $languageType,
                'languageFile' => $languageFeature,
                'data' => $data['default']
            ]);
        });
        
        array_push($language_names, 'default', 'action');
        $dataTable->rawColumns($language_names);
        return $dataTable->make(true);
    }
    
    public function getTranslatorData(Request $request)
    {
        $languageType = $request->language_type;
        $languageFeature = $request->language_file;
        $languageSlug = $request->language_slug;
        $organizationId = $request->organization_id;
        $languageData = (new TranslatorHelper)->getOrganizationLanguageData($organizationId, $languageFeature, $languageType, $languageSlug);
        $defaultLanguageData = $languageData['default'] ?? [];
        unset($languageData['default']);
        
        $view = view('translator::edit', [
            'languageFeature' => $languageFeature, 
            'languageType' => $languageType, 
            'defaultLanguageData' => $defaultLanguageData, 
            'languageData' => $languageData,
            'organization_id' => $organizationId
        ])->render();
        
        return response()->json([
            'status' => true,
            'html'   => $view
        ]);
        
    }


    public function updateKeys(Request $request)
    {
     Translator::updateKeys($request['organization_id'], $request['feature'], $request['type'], $request['language']);
    if (Gate::allows('superadmin')) {
           App::setLocale('en');
           }
           if (Gate::allows('admin')) {
           App::setLocale(Auth::user()->language);
           }
        return response()->json([
                    'status' => config('constants.STATUS_SUCCESS'),
                    'message' => [[Lang::get('message.msg_sucessfully_created')]],
                        ], config('constants.STATUS_SUCCESS'));
    }
    
    public function getOrganizationLanguageList(Request $request)
    {
        $organizationId = $request->organization_id;
        // get Type and Feature
        $languageType= config('translator.type.'.$request->language_type);
        $languageFeature= config('translator.features.'.$request->language_feature);
        $orgLanguages = (new TranslatorHelper)->getOrgLanguages($organizationId);
        $tableColumns = [];
        foreach ($orgLanguages as $language) {
            $tableColumns[] = ['data' => strtolower($language), 'name' => strtolower($language), 'class' => 'prevent-overflow col-width20'];
        }
        $tableColumns = json_encode($tableColumns);
        $view = view('translator::language-list', [
            'orgLanguages' => $orgLanguages, 
            'tableColumns' => $tableColumns,
            'languageType'=>$languageType,
            'languageFeature'=>$languageFeature
            ])->render();
        
        return response()->json([
            'status' => true,
            'html'   => $view
        ]);
    }
    

}
