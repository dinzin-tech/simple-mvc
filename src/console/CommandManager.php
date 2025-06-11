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

    public function init()
    {
        define('BASE_PATH_IN_COMMANDS', dirname(__DIR__, 5));
        
        $this->registerCommands();
    }

    protected function registerCommands()
    {
        // This method can be used to dynamically register commands if needed.
        // Currently, commands are statically defined in the $commands array.
        // You can add logic here to load commands from a directory or configuration file.

        // Example: Load commands from a specific directory
        $commandFiles = glob(BASE_PATH_IN_COMMANDS . '/commands/*.php');

        foreach ($commandFiles as $commandFile) {
            $commandClass = 'App\\Commands\\' . basename($commandFile, '.php');
            $commandName = strtolower(str_replace('Command', '', basename($commandFile, '.php')));

            if (class_exists($commandClass) && !isset($this->commands[$commandName]) && method_exists($commandClass, 'execute')) {
                $this->commands[$commandName] = $commandClass;
            }
        }

        
    }

    public function run(array $args)
    {
        $command = $args[1] ?? 'help';
        $arguments = array_slice($args, 2);

        if (!isset($this->commands[$command])) {
            echo "Command not recognized. Use 'php bin/console help' for assistance.\n";
            return;
        }

        $commandClass = $this->commands[$command];
        (new $commandClass())->execute($arguments);
    }
}