<?php

namespace App\Http\Requests\APIRequest;

use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Foundation\Http\FormRequest;
use function GuzzleHttp\json_encode;

abstract class CustomFormRequest extends FormRequest
{
    
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    abstract public function rules();
    
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    abstract public function authorize();
    
    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function failedValidation(Validator $validator)
    {

        $errors = (new ValidationException($validator))->errors();
        $res = array();
        foreach($errors as $val) {
            foreach ($val as $message) {
                $res[] = $message;
            }
        }
        $data = ($res);

        throw new HttpResponseException(response()->json(['errors' => $data,
            'status'=>false], JsonResponse::HTTP_UNPROCESSABLE_ENTITY));
    }
}
