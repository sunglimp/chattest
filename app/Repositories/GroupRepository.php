<?php

namespace App\Repositories;

use App\Models\Group;
use LaravelUtility\Repository\Repositories\CacheRepository;

class GroupRepository extends CacheRepository
{
    public function __construct(Group $group)
    {
        parent::__construct($group);
    }
    
}
