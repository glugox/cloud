<?php

namespace Glugox\Orchestrator\Providers;

use Glugox\Orchestrator\Support\ModuleOrchestrator;
use Illuminate\Support\ServiceProvider;

class OrchestratorServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ModuleOrchestrator::class);

        $this->mergeConfigFrom(
            __DIR__ . '/../../config/glugox.php',
            'glugox'
        );
    }

    public function boot(ModuleOrchestrator $orchestrator): void
    {
        $config = $this->app['config']->get('glugox');

        $paths = $config['auto_discover']['paths'] ?? [];
        $enabled = $config['modules']['enabled'] ?? [];
        $disabled = $config['modules']['disabled'] ?? [];

        $orchestrator->discover($paths, $enabled, $disabled);
        $orchestrator->registerDiscoveredModules();

        $this->app->booted(static function () use ($orchestrator): void {
            $orchestrator->bootRegisteredModules();
        });

        $this->publishes([
            __DIR__ . '/../../config/glugox.php' => config_path('glugox.php'),
        ], 'glugox-config');
    }
}
