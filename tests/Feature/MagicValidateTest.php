<?php

use Symfony\Component\Console\Command\Command;

it('Validate json config', function () {

    $configPath = __DIR__.'/../../specs/modules/crm.json';

    $this->artisan('magic:validate', [
        '--config' => $configPath,
    ])->expectsOutput('Configuration file is valid.')
        ->assertExitCode(Command::SUCCESS);

});
