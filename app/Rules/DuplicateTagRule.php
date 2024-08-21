<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\User;
use Illuminate\Support\Facades\Input;
use App\Models\Tag;
use Illuminate\Support\Facades\Auth;

class DuplicateTagRule implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
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
        if (empty(Auth::user())) {
            $userToken = Input::get('token') ?? '';
            $userDetails = User::getUserDetailByToken($userToken);
        } else {
            $userDetails = Auth::user();
        }
        
        $loggedInUserRole = $userDetails->role_id ?? 0;
        $isAdmin = in_array($loggedInUserRole, config('constants.ADMIN_ROLE_IDS'));
        $organizationId = $userDetails->organization_id ?? null;
        if ($isAdmin == true) {
            $isTagExist = Tag::where('organization_id', $organizationId)
            ->where($attribute, $value)
            ->get();
            
            if ($isTagExist->isEmpty()) {
                return true;
            } else {
                return false;
            }
        }
        else {
            $isTagExist = Tag::where('organization_id', $organizationId)
            ->where($attribute, $value)
            ->where('is_admin_tag',1)        
            ->get();
            if ($isTagExist->isEmpty()) {
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('message.tag_duplicate');
    }
}
