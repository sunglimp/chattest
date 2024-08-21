<?php

namespace App\Http\Requests\Preferences;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Session;

class OfflineFormRequest extends FormRequest
{
    public function __construct() {

    }
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(Request $request)
    {
        return [
            'message' => 'required',
            'organization_id' => 'required',
            'email_id.*' => 'bail|required_with:email_slider|email',
            'subject' => 'required_with:email_slider',
            'email_body' => 'required_with:email_slider',
            'session_push'=> 'required_with:wa_push_slider',
            'api' => 'required_if:session_push,2,3',
            'template_id' => 'required_if:session_push,2,3',
            'free_api' => 'required_if:session_push,1,3',
            'free_template_id' => 'required_if:session_push,1,3',
            'bot_id' => 'required_with:wa_push_slider',
            'token' => 'required_with:wa_push_slider',
            'send_email_on_qc' => 'required_with:email_slider'
        ];
    }

    public function messages()
    {
        return [
            'message.required' => default_trans(Session::get('userOrganizationId').'/permission.validation_messages.message_required', __('default/permission.validation_messages.message_required')),
            'email_id.*.required_with' => default_trans(Session::get('userOrganizationId').'/permission.validation_messages.email_required', __('default/permission.validation_messages.email_required')),
            'email_id.*.email' => default_trans(Session::get('userOrganizationId').'/permission.validation_messages.email_format', __('default/permission.validation_messages.email_format')),
            'subject.required_with' => default_trans(Session::get('userOrganizationId').'/permission.validation_messages.subject_required', __('default/permission.validation_messages.subject_required')),
            'email_body.required_with' => default_trans(Session::get('userOrganizationId').'/permission.validation_messages.email_body_required', __('default/permission.validation_messages.email_body_required')),

            'api.required_if' => default_trans(Session::get('userOrganizationId').'/permission.validation_messages.api_required', __('default/permission.validation_messages.api_required')),
            'template_id.required_if' => default_trans(Session::get('userOrganizationId').'/permission.validation_messages.template_id_required', __('default/permission.validation_messages.template_id_required')),
            'bot_id.required_with' => default_trans(Session::get('userOrganizationId').'/permission.validation_messages.bot_id_required', __('default/permission.validation_messages.bot_id_required')),
            'token.required_with' => default_trans(Session::get('userOrganizationId').'/permission.validation_messages.classified_token_required', __('default/permission.validation_messages.classified_token_required')),
            'free_api.required_if' => default_trans(Session::get('userOrganizationId').'/permission.validation_messages.free_api_required', __('default/permission.validation_messages.free_api_required')),
            'free_template_id.required_if' => default_trans(Session::get('userOrganizationId').'/permission.validation_messages.free_template_id_required', __('default/permission.validation_messages.free_template_id_required')),
            'session_push.required_with' => default_trans(Session::get('userOrganizationId').'/permission.validation_messages.session_push_required', __('default/permission.validation_messages.session_push_required')),
            'send_email_on_qc.required_with' => default_trans(Session::get('userOrganizationId').'/permission.validation_messages.send_email_on_qc_required', __('default/permission.validation_messages.send_email_on_qc_required')),
        ];
    }

    /**
     * Prepare the data for validation
     *
     * Method to manipulate any of the request input values.
     * Because the base FormRequest class extends the Request class
     * we have access to the merge helper method which
     * we can use to update just the input values that we need to.
     * We can also access the input values themselves like properties on the class.
     *
     */
    protected function prepareForValidation()
    {
        $email_ids = [];
        if ($this->email_slider) {
            //Here email we are reciving as comma seperated so we make it array
            $email_ids = explode(',', rtrim($this->email_id, ','));
        }

        $this->merge(['email_id' => $email_ids]);
    }
}
