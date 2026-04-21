<?php

namespace Inovector\Mixpost\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class PublishAssetsCommand extends Command
{
    public $signature = 'mixpost:publish-assets {--force=}';

    public $description = 'Publish compiled assets to your public folder';

    public function handle(): int
    {
        $force = boolval($this->option('force'));

        if (! $force && File::exists(public_path('vendor/genie-pro'))) {
            $this->line('Your application already have the Mixpost assets');

            if (! $this->confirm('Do you want to rewrite?')) {
                return self::FAILURE;
            }
        }

        File::deleteDirectory(public_path('vendor/genie-pro'));
        File::copyDirectory(__DIR__.'/../../resources/dist/vendor/genie-pro', public_path('vendor/genie-pro'));
        File::copyDirectory(__DIR__.'/../../resources/img/favicon', public_path('vendor/genie-pro/favicon'));

        $this->info('Assets was published to [public/vendor/genie-pro]');

        return self::SUCCESS;
    }
}
