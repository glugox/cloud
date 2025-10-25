<?php

namespace Glugox\ModuleGenerator\Commands;

use Glugox\ModuleGenerator\ModuleGenerator;
use Glugox\ModuleGenerator\ModuleSpecification;
use Illuminate\Console\Command;

class GenerateModuleCommand extends Command
{
    protected $signature = 'glugox:module:generate {spec : Path to the module specification (JSON/YAML)} {--force : Overwrite an existing module}';

    protected $description = 'Generate a Glugox module from a specification file.';

    public function handle(ModuleGenerator $generator): int
    {
        $specPath = $this->argument('spec');
        $force = (bool) $this->option('force');

        $specification = ModuleSpecification::fromFile($specPath);
        $modulePath = $generator->generate($specification, force: $force);

        $this->components->info("Module generated at {$modulePath}.");

        return self::SUCCESS;
    }
}
