<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\User;


class CheckOrganizationSeats implements Rule
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

        $userCount = User::where('organization_id', $this->request->organization_id)->where('role_id', '!=', config('constants.user.role.admin'))->count('id');
        if ($this->request->seat_alloted < $userCount) {
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
        return 'Current active users are greater than the inserted value.';
    }
}
