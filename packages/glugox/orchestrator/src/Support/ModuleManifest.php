<?php

namespace Glugox\Orchestrator\Support;

class ModuleManifest
{
    public function __construct(
        public readonly string $name,
        public readonly string $slug,
        public readonly string $namespace,
        public readonly string $class,
        public readonly string $description,
        public readonly string $version,
        public readonly array $providers,
        public readonly array $routes,
        public readonly string $path,
        public readonly bool $enabled = true,
    ) {
    }

    public static function fromArray(array $data, string $path, bool $enabled = true): self
    {
        return new self(
            name: (string) ($data['name'] ?? $data['slug'] ?? 'module'),
            slug: (string) ($data['slug'] ?? ''),
            namespace: (string) ($data['namespace'] ?? ''),
            class: (string) ($data['class'] ?? ''),
            description: (string) ($data['description'] ?? ''),
            version: (string) ($data['version'] ?? '1.0.0'),
            providers: array_map('strval', array_values($data['providers'] ?? [])),
            routes: array_map('strval', array_values($data['routes'] ?? [])),
            path: $path,
            enabled: $enabled,
        );
    }
}
