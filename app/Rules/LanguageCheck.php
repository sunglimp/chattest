<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class LanguageCheck implements Rule
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
        $organizationLanguages = $this->request->language;
        $userLanguages = \App\User::where('organization_id', $organizationId)->where('language','!=','')->groupBy('language')->pluck('language')->toArray();  
            if (!empty($userLanguages)) {
                $languageDiff = array_diff($userLanguages, $organizationLanguages);
                if(!empty($languageDiff))
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
        return 'Unchecked Language is associated with user.';
    }
}
