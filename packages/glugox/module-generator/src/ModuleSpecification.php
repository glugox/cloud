<?php

namespace Glugox\ModuleGenerator;

use Illuminate\Filesystem\Filesystem;
use InvalidArgumentException;

class ModuleSpecification
{
    public function __construct(
        public readonly string $name,
        public readonly string $slug,
        public readonly string $namespace,
        public readonly string $description,
        public readonly string $version,
        public readonly array $providers,
        public readonly array $routes,
    ) {
    }

    public static function fromFile(string $path): self
    {
        $filesystem = new Filesystem();

        if (! $filesystem->exists($path)) {
            throw new InvalidArgumentException("Specification file [{$path}] does not exist.");
        }

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return match ($extension) {
            'json' => self::fromArray(json_decode($filesystem->get($path), true) ?? []),
            'yaml', 'yml' => self::fromArray(self::parseYaml($filesystem->get($path))),
            default => throw new InvalidArgumentException("Unsupported specification format [{$extension}]."),
        };
    }

    public static function fromArray(array $data): self
    {
        $name = trim((string) ($data['name'] ?? ''));
        $slug = trim((string) ($data['slug'] ?? ''));
        $namespace = trim((string) ($data['namespace'] ?? ''));
        $description = trim((string) ($data['description'] ?? ''));
        $version = trim((string) ($data['version'] ?? '1.0.0'));
        $providers = array_map('strval', array_values(array_filter($data['providers'] ?? [], 'strlen')));
        $routes = array_map('strval', array_values(array_filter($data['routes'] ?? [], 'strlen')));

        if ($name === '' || $slug === '' || $namespace === '') {
            throw new InvalidArgumentException('Specification must define name, slug and namespace.');
        }

        return new self($name, $slug, $namespace, $description, $version, $providers, $routes);
    }

    public function className(): string
    {
        return rtrim($this->namespace, '\') . '\' . $this->studlySlug() . 'Module';
    }

    public function directoryName(): string
    {
        return $this->studlySlug();
    }

    public function studlySlug(): string
    {
        return str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $this->slug)));
    }

    protected static function parseYaml(string $contents): array
    {
        if (function_exists('yaml_parse')) {
            $parsed = yaml_parse($contents);

            if (is_array($parsed)) {
                return $parsed;
            }
        }

        $data = [];
        $currentKey = null;

        foreach (preg_split('/\r?\n/', $contents) as $line) {
            $trimmed = trim($line);

            if ($trimmed === '' || str_starts_with($trimmed, '#')) {
                continue;
            }

            if (preg_match('/^([A-Za-z0-9_]+):\s*(.*)$/', $trimmed, $matches) === 1) {
                $currentKey = $matches[1];
                $value = $matches[2];

                if ($value === '') {
                    $data[$currentKey] = [];
                    continue;
                }

                $data[$currentKey] = self::normalizeYamlValue($value);
                $currentKey = null;

                continue;
            }

            if ($currentKey !== null && str_starts_with($trimmed, '- ')) {
                $value = substr($trimmed, 2);
                $data[$currentKey] ??= [];
                $data[$currentKey][] = self::normalizeYamlValue($value);
            }
        }

        return $data;
    }

    protected static function normalizeYamlValue(string $value): mixed
    {
        $value = trim($value);

        if ($value === '') {
            return $value;
        }

        if ((str_starts_with($value, '"') && str_ends_with($value, '"')) || (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
            return substr($value, 1, -1);
        }

        if (is_numeric($value)) {
            return $value + 0;
        }

        if ($value === 'true') {
            return true;
        }

        if ($value === 'false') {
            return false;
        }

        if (str_starts_with($value, '[') && str_ends_with($value, ']')) {
            $decoded = json_decode(str_replace("'", '"', $value), true);

            return $decoded ?? $value;
        }

        return $value;
    }
}
