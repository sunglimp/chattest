<?php

namespace App\Repositories;

use App\Models\Organization;
use LaravelUtility\Repository\Repositories\CacheRepository;

class OrganizationRepository extends CacheRepository
{
    
    public function __construct(Organization $organization)
    {
        parent::__construct($organization);
    }
    
}
