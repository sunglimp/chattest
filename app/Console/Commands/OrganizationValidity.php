<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Organization;
use Carbon\Carbon;
use App\User;
use App\Mail\OrganizationValidityMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class OrganizationValidity extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'organization:validity';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command is used for checking the validity or reminding the admin before expire';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
            Log::Debug('===========Organization Account Validity=================');
            // Find the organization which are expired
            $currentDate           = Carbon::yesterday()->toDateString();
            $expiredOrganizationId = Organization::where('validity_date', '<=', $currentDate)->where('status', Organization::STATUS_ACTIVE)->pluck('id');

            // Update organization and user status to deactivate
            Organization::changeOrganizationsStatus($expiredOrganizationId);
            User::changeUsersStatus($expiredOrganizationId);

            // Find the organization which are going to expire after some days and mail to admin
            $validityDate         = [];
            $validityReminderDate = config('constants.ORGANIZATION_VALIDITY_REMINDER_DAYS');
            foreach ($validityReminderDate as $days) {
                $validityDate[] = Carbon::today()->addDays($days)->toDateString();
            }

            $organizations = Organization::getOrganizationListWithinValidDate($validityDate);
            Log::Debug($organizations);
            if (count($organizations) > 0) {
                Log::Debug("==============Sending Account to be expired mails=========");
                Log::Debug(config('constants.ORGANIZATION_VALIDITY_MAIL_ID'));
                Mail::to(config('constants.ORGANIZATION_VALIDITY_MAIL_ID'))->send(new OrganizationValidityMail('Account Validity Summary Report', $organizations));
            }
    }

}
