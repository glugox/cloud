<?php

namespace Glugox\Module\Support;

use Glugox\Module\Contracts\ModuleContract;
use Glugox\Orchestrator\Support\ModuleManifest;
use Illuminate\Contracts\Container\Container;

abstract class AbstractModule implements ModuleContract
{
    protected Container $app;

    protected ModuleManifest $manifest;

    public function __construct(Container $app)
    {
        $this->app = $app;
    }

    public function withManifest(ModuleManifest $manifest): void
    {
        $this->manifest = $manifest;
    }

    public function getName(): string
    {
        return $this->manifest->name;
    }

    public function getSlug(): string
    {
        return $this->manifest->slug;
    }

    public function getVersion(): string
    {
        return $this->manifest->version;
    }

    public function getDescription(): ?string
    {
        return $this->manifest->description;
    }

    public function providers(): array
    {
        return $this->manifest->providers;
    }

    public function routes(): array
    {
        return $this->manifest->routes;
    }

    public function register(): void
    {
        // Modules may override to add bindings.
    }

    public function boot(): void
    {
        // Modules may override to perform boot logic.
    }

    protected function basePath(string $path = ''): string
    {
        return rtrim($this->manifest->path, DIRECTORY_SEPARATOR)
            . ($path ? DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : '');
    }
}
