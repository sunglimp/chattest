<?php

namespace App\Observers;

use App\Models\Organization;
use App\Models\Group;
use App\Models\OrganizationRolePermission;
use App\Models\PermissionSetting;
use App\Models\Tag;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\User;

class OrganizationObserver
{

    protected $cacheKeyOrganization = 'organization:%d';

    /**
     * Handle the User "created" event.
     *
     * @param  \App\User  $user
     * @return void
     */
    public function created(Organization $organization)
    {
        $userId = 1;//Auth::user()->id;

        $organizationRolePermission = [
            [
                'organization_id' => $organization->id,
                'role_id'         => config('constants.user.role.admin'),
                'permission_id'   => config('constants.ROLE_PERMISSION_ID'),
                'created_by'      => $userId,
                'created_at'      => Carbon::now()->timestamp
            ],
            [
                'organization_id' => $organization->id,
                'role_id'         => config('constants.user.role.manager'),
                'permission_id'   => config('constants.ROLE_PERMISSION_ID'),
                'created_by'      => $userId,
                'created_at'      => Carbon::now()->timestamp
            ],
            [
                'organization_id' => $organization->id,
                'role_id'         => config('constants.user.role.team_lead'),
                'permission_id'   => config('constants.ROLE_PERMISSION_ID'),
                'created_by'      => $userId,
                'created_at'      => Carbon::now()->timestamp
            ],
            [
                'organization_id' => $organization->id,
                'role_id'         => config('constants.user.role.associate'),
                'permission_id'   => config('constants.ROLE_PERMISSION_ID'),
                'created_by'      => $userId,
                'created_at'      => Carbon::now()->timestamp
            ]
        ];

        $defaultGroup = [
            'organization_id' => $organization->id,
            'name'            => config('constants.GROUP_DEFAULT'),
            'created_by'      => $userId,
            'created_at'      => Carbon::now()->timestamp
        ];

        OrganizationRolePermission::insert($organizationRolePermission);
        Group::create($defaultGroup);

        $permissionSetting = [
            [
                'organization_id' => $organization->id,
                'permission_id'   => config('constants.PERMISSION.SEND-ATTACHMENT'),
                'settings'        => json_encode(['size' => 5]),
                'created_at'      => Carbon::now()->timestamp
            ],
            [
                'organization_id' => $organization->id,
                'permission_id'   => config('constants.PERMISSION.AUTO-CHAT-TRANSFER'),
                'settings'        => json_encode(['hour' => 0, 'minute' => 1, 'second' => 0,'transfer_limit'=>config('constants.AUTO_CHAT_TRANSFER_LIMIT')]),
                'created_at'      => Carbon::now()->timestamp
            ],
            [
                'organization_id' => $organization->id,
                'permission_id'   => config('constants.PERMISSION.CHAT-NOTIFIER'),
                'settings'        => json_encode(['hour' => 0, 'minute' => 2, 'second' => 0]),
                'created_at'      => Carbon::now()->timestamp
            ],
            [
                'organization_id' => $organization->id,
                'permission_id'   => config('constants.PERMISSION.TIMEOUT'),
                'settings'        => json_encode(['hour' => 0, 'minute' => 2, 'second' => 0]),
                'created_at'      => Carbon::now()->timestamp
            ],
            [
                'organization_id' => $organization->id,
                'permission_id'   => config('constants.PERMISSION.CHAT-FEEDBACK'),
                'settings'        => json_encode(['feedback' => 'NPS']),
                'created_at'      => Carbon::now()->timestamp
            ],
            [
                'organization_id' => $organization->id,
                'permission_id'   => config('constants.PERMISSION.BAN-USER'),
                'settings'        => json_encode(['days' => 1]),
                'created_at'      => Carbon::now()->timestamp
            ],
            [
                'organization_id' => $organization->id,
                'permission_id'   => config('constants.PERMISSION.OFFLINE-FORM'),
                'settings'        => json_encode(['message' => 'No agent available at this time', 'thank_you_message' => 'Thank you for submitting your query. All our agents are offline at the moment, we will revert back to you once online.']),
                'created_at'      => Carbon::now()->timestamp
            ],
            [
                'organization_id' => $organization->id,
                'permission_id'   => config('constants.PERMISSION.AUDIO-NOTIFICATION'),
                'settings'        => json_encode(['notificationEvents' => array_keys(config('config.NOTIFICATION_EVENTS'))]),
                'created_at'      => Carbon::now()->timestamp
            ],
            [
                'organization_id' => $organization->id,
                'permission_id'   => config('constants.PERMISSION.ARCHIVE_CHAT'),
                'settings'        => json_encode(['archive_type' => 1]),
                'created_at'      => Carbon::now()->timestamp
            ],
            [
                'organization_id' => $organization->id,
                'permission_id'   => config('constants.PERMISSION.SESSION_TIMEOUT'),
                'settings'        => json_encode(['hour' => 2, 'minute' => 59, 'second' => 59, 'max_hours'=>config('constants.LAST_ACTIVITY_SESSION_TIME')/60]),
                'created_at'      => Carbon::now()->timestamp
            ],
            [
                'organization_id' => $organization->id,
                'permission_id'   => config('constants.PERMISSION.CUSTOMER-INFORMATION'),
                'settings'        => json_encode(['whatsapp' => ['client_display_attribute'=> config('constants.CUSTOMER_CHAT_LABEL.WHATSAPP.NUMBER')]]),
                'created_at'      => Carbon::now()->timestamp
            ],
        ];

        PermissionSetting::insert($permissionSetting);
    }

    public function deleted(Organization $organization)
    {
        User::where('organization_id', $organization->id)->delete();
        cache()->forget(sprintf($this->cacheKeyOrganization, $organization->id));
    }

    public function updated(Organization $organization)
    {
        cache()->forget(sprintf($this->cacheKeyOrganization, $organization->id));
    }
}
