<?php

declare(strict_types=1);

namespace Svidskiy\Modulith\Commands;

use Illuminate\Console\Command;

final class InstallCommand extends Command
{
    protected $signature = 'modulith:install';

    protected $description = 'Install Modulith and publish its assets.';

    public function handle(): int
    {
        $this->call('vendor:publish', [
            '--tag' => 'modulith-config',
        ]);

        $this->components->info('Modulith installed.');

        return self::SUCCESS;
    }
}
