<?php

namespace App\Providers;

use App\ {
    Models\ChatChannel,
    Models\Group,
    Models\Summary,
    User
};
use Exception;
use Illuminate\Support\ {
    Collection,
    ServiceProvider
};

class MacroServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->summarize();
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    private function summarize()
    {
        Collection::macro('summarize', function ($now, $field) {
            return $this->map(function ($collection) use ($now, $field) {
                        
                $agentId = $collection->agent_id ?? null;
                $organizationId = $collection->organization_id  ?? null;
                
                if (!empty($agentId) && empty($organizationId)) {
                    $organizationId = User::find($agentId)->organization_id;
                } else {
                    if (empty($collection->group_id)) {
                        if (!empty($collection->chat_channel_id)) {
                            $chatChannel = ChatChannel::where('id', $collection->chat_channel_id)
                                    ->with('group')
                                    ->first();
                            $organizationId = $chatChannel->group->organization_id;
                        }
                    } else {
                        $organizationId = Group::find($collection->group_id)->organization_id;
                    }
                }
                if (!empty($organizationId)) {
                    $update = [
                        $field => $collection->$field
                    ];
                    try {
                        return Summary::updateSummary(
                            $organizationId,
                            $agentId,
                            $now,
                            $update
                        );
                    } catch (Exception $e) {
                        \Log::error($e->getMessage());
                    }
                }
            });
        });
    }
}
