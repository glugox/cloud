<?php

namespace App\Providers;

use Cloud\Packages\ConfigLoader\FileConfigLoader;
use Cloud\Packages\ConfigValidator\ConfigValidator;
use Cloud\Packages\ModuleRegistry\ModuleRegistry;
use Illuminate\Support\ServiceProvider;

class ModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(FileConfigLoader::class, fn () => new FileConfigLoader());
        $this->app->singleton(ConfigValidator::class, fn () => new ConfigValidator());

        $this->app->singleton(ModuleRegistry::class, function ($app) {
            return new ModuleRegistry(
                base_path('modules'),
                $app->make(FileConfigLoader::class),
                $app->make(ConfigValidator::class)
            );
        });
    }

    public function boot(): void
    {
        // Placeholder for future boot logic.
    }
}
