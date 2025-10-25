<?php

namespace Glugox\ModuleGenerator\Providers;

use Glugox\ModuleGenerator\Commands\GenerateModuleCommand;
use Glugox\ModuleGenerator\ModuleGenerator;
use Illuminate\Support\ServiceProvider;

class ModuleGeneratorServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ModuleGenerator::class);
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                GenerateModuleCommand::class,
            ]);
        }
    }
}
