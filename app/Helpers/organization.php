<?php

use App\Models\Organization;
/**
 * Organization Helpers
 */

if(!function_exists('organization_exists'))
{
    function organization_exists($organizationId)
    {
        if(cache()->has('organization_' . $organizationId))
        {
            return true;
        }
        else
        {
            if($organization = organization($organizationId))
            {
               return true; 
            }
        }
        return false;
    }
}


if(!function_exists('organization'))
{
    function organization($organizationId)
    {
        return cache()->tags('organization')->rememberForever('organization_' . $organizationId, function() use ($organizationId){
            $organization = Organization::where('id', $organizationId);
            return $organization->exists() ? $organization->first() : null;
        });
        
    }
}

