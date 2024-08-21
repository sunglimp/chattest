<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\User;
use Illuminate\Support\Facades\Session;

class AdminExist implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($request)
    {
        $this->request= $request;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $organizationId = $this->request->organization_id;
        $roleId =  $this->request->role_id;
        $userId = $this->request->user_id;
        if ($roleId == config('constants.user.role.admin')) {
            $admin = User::where('organization_id', $organizationId)->where('id', '!=', $userId)->where('role_id', $roleId)->first();

            if (is_object($admin)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return default_trans(Session::get('userOrganizationId').'/user_list.validation_messages.admin_exist', __('default/user-list.validation_messages.admin_exist'));
    }
}
