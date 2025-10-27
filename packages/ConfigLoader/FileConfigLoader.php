<?php

namespace Cloud\Packages\ConfigLoader;

use JsonException;
use RuntimeException;

class FileConfigLoader
{
    /**
     * Load a JSON configuration file into an array.
     */
    public function load(string $path): array
    {
        if (! is_file($path)) {
            throw new RuntimeException("Configuration file not found: {$path}");
        }

        $contents = file_get_contents($path);
        if ($contents === false) {
            throw new RuntimeException("Unable to read configuration file: {$path}");
        }

        try {
            /** @var array<string, mixed> $decoded */
            $decoded = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new RuntimeException(
                sprintf('Invalid JSON in %s: %s', $path, $exception->getMessage()),
                previous: $exception
            );
        }

        return $decoded;
    }
}
