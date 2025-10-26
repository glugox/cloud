<?php

namespace Cloud\Packages\ModuleRegistry;

use Cloud\Packages\ConfigLoader\FileConfigLoader;
use Cloud\Packages\ConfigValidator\ConfigValidator;

class ModuleRegistry
{
    public function __construct(
        private readonly string $directory,
        private readonly FileConfigLoader $loader,
        private readonly ConfigValidator $validator,
    ) {
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function all(): array
    {
        $modules = [];
        $path = rtrim($this->directory, DIRECTORY_SEPARATOR);

        if (! is_dir($path)) {
            return [];
        }

        /** @var array<int, string> $files */
        $files = glob($path.DIRECTORY_SEPARATOR.'*.json') ?: [];

        foreach ($files as $file) {
            $summary = [
                'file' => $file,
                'valid' => false,
                'errors' => [],
                'config' => null,
            ];

            try {
                $config = $this->loader->load($file);
                $summary['config'] = $config;
                $validation = $this->validator->validate($config);
                $summary['valid'] = $validation['valid'];
                $summary['errors'] = $validation['errors'];
                $summary['name'] = $config['app']['name'] ?? basename($file, '.json');
            } catch (\Throwable $exception) {
                $summary['errors'][] = $exception->getMessage();
            }

            $modules[] = $summary;
        }

        return $modules;
    }
}
