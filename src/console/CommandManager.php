<?php

namespace Core\Console;

class CommandManager
{
    protected $commands = [
        'make:controller' => 'Core\Console\Commands\MakeController',
        'make:model' => 'Core\Console\Commands\MakeModel',
        'make:view' => 'Core\Console\Commands\MakeView',
        'migrations:create' => 'Core\Console\Commands\MigrationGenerateCommand',
        'migrations:exec' => 'Core\Console\Commands\MigrationRunCommand',
        'migrations' => 'Core\Console\Commands\MigrationRunCommand',
        'help' => 'Core\Console\Commands\HelpCommand',
        '-h' => 'Core\Console\Commands\HelpCommand',
        'console:setup' => 'Core\Console\Commands\ConsoleSetupCommand',
        'route:cache' => 'Core\Console\Commands\RouteCacheCommand',
        'route:clear' => 'Core\Console\Commands\RouteClearCommand',
    ];

    public function init()
    {
        // Try to rely on the current working directory first (since users run php bin/console from app root)
        $cwd = getcwd();
        if (file_exists($cwd . '/bin/console') || file_exists($cwd . '/public/index.php')) {
            define('BASE_PATH_IN_COMMANDS', $cwd);
        } else {
            // Fallback: assuming vendor/dinzin-tech/simple-mvc/src/console
            define('BASE_PATH_IN_COMMANDS', dirname(__DIR__, 4));
        }
        
        // Router and Models rely on BASE_PATH which is normally defined in Kernel.php
        if (!defined('BASE_PATH')) {
            define('BASE_PATH', BASE_PATH_IN_COMMANDS);
        }
        
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