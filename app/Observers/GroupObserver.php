<?php

namespace App\Observers;

use App\Models\Group;

class GroupObserver
{
    protected $cacheKeyOrganizationId = 'groups:organization_id:%d';
    /**
     * Handle the group "created" event.
     *
     * @param  \App\Group  $group
     * @return void
     */
    public function created(Group $group)
    {
        cache()->forget(sprintf($this->cacheKeyOrganizationId, $group->organization_id));
    }

    /**
     * Handle the group "updated" event.
     *
     * @param  \App\Group  $group
     * @return void
     */
    public function updated(Group $group)
    {
        //
    }

    /**
     * Handle the group "deleted" event.
     *
     * @param  \App\Group  $group
     * @return void
     */
    public function deleted(Group $group)
    {
        cache()->forget(sprintf($this->cacheKeyOrganizationId, $group->organization_id));
    }

    /**
     * Handle the group "restored" event.
     *
     * @param  \App\Group  $group
     * @return void
     */
    public function restored(Group $group)
    {
        //
    }

    /**
     * Handle the group "force deleted" event.
     *
     * @param  \App\Group  $group
     * @return void
     */
    public function forceDeleted(Group $group)
    {
        //
    }
}
