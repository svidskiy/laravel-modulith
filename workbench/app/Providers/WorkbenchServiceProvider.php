<?php

declare(strict_types=1);

namespace Workbench\App\Providers;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Support\ServiceProvider;

use function Orchestra\Testbench\workbench_path;

final class WorkbenchServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        /** @var Repository $config */
        $config = $this->app->make('config');

        $config->set('modulith.path', workbench_path('app/Modules'));
        $config->set('modulith.namespace', 'Workbench\\App\\Modules');
        $config->set('modulith.cache.enabled', false);
    }
}
