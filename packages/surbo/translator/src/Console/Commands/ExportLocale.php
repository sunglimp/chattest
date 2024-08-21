<?php

namespace Surbo\Translator\Console\Commands;

use App\Models\Organization;
use Illuminate\Console\Command;
use Surbo\Translator\Translator;

class ExportLocale extends Command
{
    private $translator;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'locale:export {type} {feature}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Upload all locate from database to files';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $type = $this->argument('type');
        $feature = $this->argument('feature');
        $organization = Organization::active()->select(['id','languages'])->get();
        foreach ($organization as $org) {
            $this->translator->exportToFiles($org, $type, $feature);
        }
    }
}
