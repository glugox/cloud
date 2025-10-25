<?php

namespace Glugox\Orchestrator\Support;

use Glugox\Module\Contracts\ModuleContract;
use Illuminate\Contracts\Container\Container;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use RuntimeException;

class ModuleOrchestrator
{
    /**
     * @var array<string, ModuleManifest>
     */
    protected array $manifests = [];

    /**
     * @var array<string, ModuleContract>
     */
    protected array $modules = [];

    public function __construct(
        protected Container $app,
        protected Filesystem $files
    ) {
    }

    /**
     * Discover module manifests from the configured directories.
     *
     * @param  array<int, string>  $paths
     * @param  array<int, string>  $enabled
     * @param  array<int, string>  $disabled
     * @return array<string, ModuleManifest>
     */
    public function discover(array $paths, array $enabled = [], array $disabled = []): array
    {
        $enabled = array_filter($enabled);
        $disabled = array_filter($disabled);

        $manifests = [];

        foreach ($paths as $path) {
            if (! $this->files->isDirectory($path)) {
                continue;
            }

            foreach ($this->files->directories($path) as $directory) {
                $manifestPath = $directory . DIRECTORY_SEPARATOR . 'module.json';

                if (! $this->files->exists($manifestPath)) {
                    continue;
                }

                $data = json_decode($this->files->get($manifestPath), true) ?? [];
                $slug = (string) ($data['slug'] ?? Str::slug(basename($directory)));

                if ($slug === '') {
                    continue;
                }

                $data['slug'] = $slug;
                $data['name'] ??= Str::headline($slug);

                $isEnabled = empty($enabled) || in_array($slug, $enabled, true);

                if (! empty($disabled) && in_array($slug, $disabled, true)) {
                    $isEnabled = false;
                }

                $manifests[$slug] = ModuleManifest::fromArray($data, $directory, $isEnabled);
            }
        }

        ksort($manifests);

        return $this->manifests = $manifests;
    }

    public function registerDiscoveredModules(): void
    {
        foreach ($this->manifests as $slug => $manifest) {
            if (! $manifest->enabled) {
                continue;
            }

            $module = $this->resolveModule($manifest);
            $this->modules[$slug] = $module;

            $module->register();

            foreach ($manifest->providers as $provider) {
                $this->app->register($provider);
            }
        }
    }

    public function bootRegisteredModules(): void
    {
        foreach ($this->modules as $slug => $module) {
            $manifest = $this->manifests[$slug];

            $module->boot();

            foreach ($manifest->routes as $route) {
                $this->loadRouteFile($manifest, $route);
            }
        }
    }

    /**
     * @return array<string, ModuleManifest>
     */
    public function manifests(): array
    {
        return $this->manifests;
    }

    /**
     * @return array<string, ModuleContract>
     */
    public function modules(): array
    {
        return $this->modules;
    }

    protected function resolveModule(ModuleManifest $manifest): ModuleContract
    {
        if ($manifest->class === '') {
            throw new RuntimeException("Module [{$manifest->name}] does not declare a module class.");
        }

        $module = $this->app->make($manifest->class);

        if (! $module instanceof ModuleContract) {
            throw new RuntimeException("Module class [{$manifest->class}] must implement " . ModuleContract::class . '.');
        }

        $module->withManifest($manifest);

        return $module;
    }

    protected function loadRouteFile(ModuleManifest $manifest, string $relativePath): void
    {
        $path = $manifest->path . DIRECTORY_SEPARATOR . ltrim($relativePath, DIRECTORY_SEPARATOR);

        if ($this->files->exists($path)) {
            require $path;
        }
    }
}
