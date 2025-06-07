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
    ];

    public function run(array $args)
    {
        $command = $args[1] ?? 'help';
        $arguments = array_slice($args, 2);

        define('BASE_PATH_IN_COMMANDS', dirname(__DIR__, 5));

        // load environment variables
        if (file_exists(BASE_PATH_IN_COMMANDS . '/.env')) {
            $dotenv = \Dotenv\Dotenv::createImmutable(BASE_PATH_IN_COMMANDS);
            $dotenv->load();
        } else {
            echo "Environment file not found. Please create a .env file.\n";
            return;
        }

        if (!isset($this->commands[$command])) {
            echo "Command not recognized. Use 'php bin/console help' for assistance.\n";
            return;
        }

        $commandClass = $this->commands[$command];
        (new $commandClass())->execute($arguments);
    }
}