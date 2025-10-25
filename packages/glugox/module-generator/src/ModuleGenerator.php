<?php

namespace Glugox\ModuleGenerator;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class ModuleGenerator
{
    public function __construct(
        protected Filesystem $files
    ) {
    }

    public function generate(ModuleSpecification $specification, ?string $basePath = null, bool $force = false): string
    {
        $basePath ??= base_path('modules');
        $moduleDirectory = $basePath . '/' . $specification->directoryName();

        if ($this->files->exists($moduleDirectory) && ! $force) {
            throw new \RuntimeException("Module directory [{$moduleDirectory}] already exists.");
        }

        $this->files->ensureDirectoryExists($moduleDirectory . '/src/Providers');
        $this->files->ensureDirectoryExists($moduleDirectory . '/routes');

        $manifest = [
            'name' => $specification->name,
            'slug' => $specification->slug,
            'namespace' => $specification->namespace,
            'class' => $specification->className(),
            'description' => $specification->description,
            'version' => $specification->version,
            'providers' => $specification->providers,
            'routes' => $specification->routes,
        ];

        $this->writeManifest($moduleDirectory, $manifest);
        $this->writeModuleClass($moduleDirectory, $specification);
        $this->writeProviderStubs($moduleDirectory, $specification);
        $this->writeRouteStubs($moduleDirectory, $specification);

        return $moduleDirectory;
    }

    protected function writeManifest(string $moduleDirectory, array $manifest): void
    {
        $this->files->put($moduleDirectory . '/module.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
    }

    protected function writeModuleClass(string $moduleDirectory, ModuleSpecification $specification): void
    {
        $namespace = $specification->namespace;
        $className = $specification->className();
        $shortClass = class_basename($className);
        $providers = var_export($specification->providers, true);
        $routes = var_export($specification->routes, true);

        $stub = <<<PHP
<?php

namespace {$namespace};

use Glugox\Module\Support\AbstractModule;

class {$shortClass} extends AbstractModule
{
    public function providers(): array
    {
        return {$providers};
    }

    public function routes(): array
    {
        return {$routes};
    }
}
PHP;

        $this->files->put($moduleDirectory . '/src/' . $shortClass . '.php', $stub . "\n");
    }

    protected function writeProviderStubs(string $moduleDirectory, ModuleSpecification $specification): void
    {
        foreach ($specification->providers as $provider) {
            $namespace = Str::beforeLast($provider, '\\');
            $class = Str::afterLast($provider, '\\');
            $relative = Str::after($namespace, $specification->namespace . '\\');
            $directory = $moduleDirectory . '/src/' . str_replace('\\\\', '/', $relative);
            $directory = rtrim($directory, '/');
            $this->files->ensureDirectoryExists($directory);

            $stub = <<<PHP
<?php

namespace {$namespace};

use Illuminate\Support\ServiceProvider;

class {$class} extends ServiceProvider
{
    public function register(): void
    {
        // Register bindings specific to the module.
    }

    public function boot(): void
    {
        // Bootstrap module services.
    }
}
PHP;
            $this->files->put($directory . '/' . $class . '.php', $stub . "\n");
        }
    }

    protected function writeRouteStubs(string $moduleDirectory, ModuleSpecification $specification): void
    {
        foreach ($specification->routes as $route) {
            $routePath = $moduleDirectory . '/' . ltrim($route, '/');
            $this->files->ensureDirectoryExists(dirname($routePath));
            $this->files->put($routePath, "<?php\n\nuse Illuminate\\Support\\Facades\\Route;\n\nRoute::middleware('web')->group(function () {\n    Route::get('/" . $specification->slug . "', function () {\n        return 'Hello from " . $specification->name . " module!';\n    });\n});\n");
        }
    }
}
