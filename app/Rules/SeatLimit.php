<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\Organization;
use App\User;
use Illuminate\Support\Facades\Session;

class SeatLimit implements Rule
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
        $organization = Organization::find($this->request->organization_id);
        $userCount = User::where('organization_id', $this->request->organization_id)->where('role_id', '!=', config('constants.user.role.admin'))->count('id');
        if ($organization->seat_alloted <= $userCount) {
            return false;
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
        return default_trans(Session::get('userOrganizationId').'/user_list.validation_messages.org_seat_limit', __('default/user-list.validation_messages.org_seat_limit'));
    }
}
