<?php

namespace Glugox\Module\Contracts;

use Glugox\Orchestrator\Support\ModuleManifest;

interface ModuleContract
{
    /**
     * Perform any container bindings for the module.
     */
    public function register(): void;

    /**
     * Bootstrap module services.
     */
    public function boot(): void;

    /**
     * Module human readable name.
     */
    public function getName(): string;

    /**
     * Module unique slug.
     */
    public function getSlug(): string;

    /**
     * Module semantic version string.
     */
    public function getVersion(): string;

    /**
     * Optional description.
     */
    public function getDescription(): ?string;

    /**
     * Service providers the module wants registered.
     *
     * @return array<int, class-string>
     */
    public function providers(): array;

    /**
     * Relative route file paths the orchestrator should load.
     *
     * @return array<int, string>
     */
    public function routes(): array;

    /**
     * Attach the resolved manifest to the module instance.
     */
    public function withManifest(ModuleManifest $manifest): void;
}
