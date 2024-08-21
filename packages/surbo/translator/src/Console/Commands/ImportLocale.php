<?php

namespace Surbo\Translator\Console\Commands;

use Illuminate\Console\Command;
use Surbo\Translator\Translator;
use Illuminate\Support\Facades\Log;

class ImportLocale extends Command
{
    private $translator;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'locale:import {type} {feature} {key}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add new key to locale database';

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
        $languages = config('config.languages');
        $default_language = config('config.default_language');
        $available_feature = config('translator.features');
        $message_type = config('translator.type');
   
        $type = $this->argument('type');
        $feature = $this->argument('feature');
        $key = $this->argument('key');

        $locale = $this->choice('For which languege you adding this key : ?', array_keys($languages), 0);
        // $this->error($locale);
        $value = $this->ask('What is value of ' . $key .': ');
        $this->error($value);
       
        // return false;
        if (!array_key_exists($type, $message_type)) {
            $this->error('Message type not available in application');
            return false;
        }

        if (!array_key_exists($feature, $available_feature)) {
            $this->error('Feature not available in application');
            return false;
        }
       
        $data['locale'] = $locale;
        if ($value !=null) {
            $data['value'] = $value; // $this->option('value');
        } else {
            $data['value'] = '';
        }
       
        $data['key'] = $key;
        $data['feature'] = $feature;
        
        Log::info($data);
        $response = $this->translator->addNewKey($type, $data);
        if ($response['status'] ==true) {
            $this->info('Key added successfuly');
        } else {
            if (!empty($response['errors'])) {
                foreach ($response['errors'] as $error) {
                    $this->error($error);
                }
            }
        }
    }
}
