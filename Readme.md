# Cloud Glugox Demo

This Laravel skeleton has been extended to showcase the **Glugox modular ecosystem**. It demonstrates how modules can be described declaratively, scaffolded with a generator, and orchestrated inside a standard Laravel application.

## Included packages

### `glugox/module`
- Defines the reusable `ModuleContract` and `AbstractModule` base class.
- Modules receive their manifest metadata and may override `register()` / `boot()` hooks.
- Route files and service providers are declared on the manifest and returned from the module instance.

### `glugox/module-generator`
- Reads JSON or YAML specifications (see `resources/glugox/blog.json`).
- Provides an Artisan command `glugox:module:generate` that scaffolds a module directory (manifest, module class, provider stub, routes).
- Output is written to `modules/{StudlyName}` by default.

### `glugox/orchestrator`
- Discovers manifests inside configured paths (default `modules/`).
- Registers and boots enabled modules, including their service providers and routes.
- Publishes configuration via `config/glugox.php` allowing modules to be enabled/disabled per slug.

## Demo workflow

1. **Describe a module** using JSON or YAML.
   ```json
   {
     "name": "Blog",
     "slug": "blog",
     "namespace": "Modules\\\\Blog",
     "description": "Simple blog module showcasing the Glugox modular workflow.",
     "providers": [
       "Modules\\\\Blog\\\\Providers\\\\BlogServiceProvider"
     ],
     "routes": [
       "routes/web.php"
     ]
   }
   ```
2. **Generate the module scaffold**
   ```bash
   php artisan glugox:module:generate resources/glugox/blog.json
   ```
   This creates `modules/Blog/` with:
   - `module.json` manifest
   - `src/BlogModule.php`
   - `src/Providers/BlogServiceProvider.php`
   - `routes/web.php`

3. **Enable the module** via configuration.
   ```php
   // config/glugox.php
   return [
       'modules' => [
           'enabled' => ['blog'],
           'disabled' => [],
       ],
   ];
   ```

4. **Boot the application.** The orchestrator service provider discovers enabled manifests, registers the Blog module, loads its provider, and wires the `/blog` route which renders the Inertia page located at `resources/js/Pages/Blog/Index.vue`.

## Directory layout

```
packages/
├── glugox/module
├── glugox/module-generator
└── glugox/orchestrator
modules/
└── Blog/
    ├── module.json
    ├── routes/web.php
    └── src/
        ├── BlogModule.php
        └── Providers/BlogServiceProvider.php
resources/glugox/
└── blog.json
```

## Commands

- `php artisan glugox:module:generate <spec>` – scaffold a module from JSON/YAML.
- `php artisan route:list` – verify routes registered by discovered modules (e.g. `/blog`).

Run the usual Laravel tooling (`php artisan serve`, `npm run dev`) to explore the demo module rendered through Inertia/Vue.
