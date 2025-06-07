<?php

namespace Core\Console;

class CommandManager
{
    protected $commands = [
        'make:controller' => 'Core\Console\Commands\MakeController',
        'make:model' => 'Core\Console\Commands\MakeModel',
        'make:view' => 'Core\Console\Commands\MakeView',
        'migrations:create' => 'Core\Console\Commands\MigrationGenerateCommand',
        'migrations:run' => 'Core\Console\Commands\MigrationRunCommand',
        'help' => 'Core\Console\Commands\HelpCommand',
        '-h' => 'Core\Console\Commands\HelpCommand',
        'console:setup' => 'Core\Console\Commands\ConsoleSetupCommand',
    ];

    public function run(array $args)
    {
        $command = $args[1] ?? 'help';
        $arguments = array_slice($args, 2);

        define('BASE_PATH_IN_COMMANDS', dirname(__DIR__, 5));

        if (!isset($this->commands[$command])) {
            echo "Command not recognized. Use 'php bin/console help' for assistance.\n";
            return;
        }

        $commandClass = $this->commands[$command];
        (new $commandClass())->execute($arguments);
    }
}