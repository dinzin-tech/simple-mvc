<?php

namespace Core\Console\Commands;

class HelpCommand
{
    public function execute(array $arguments)
    {
        $title = "
      _                 _                                    
     (_)               | |                                   
  ___ _ _ __ ___  _ __ | | ___   ______   _ __ _____   _____ 
 / __| | '_ ` _ \| '_ \| |/ _ \ |______| | '_ ` _ \ \ / / __|
 \__ \ | | | | | | |_) | |  __/          | | | | | \ V / (__ 
 |___/_|_| |_| |_| .__/|_|\___|          |_| |_| |_|\_/ \___|
                 | |                                         
                 |_|                                         
";
        echo $title . "\n\r";
        echo "  v1.0.0\n";
        echo "  by Dinzin Tech\n";
        echo "  Welcome to the Simple MVC Console!\n";
        echo "  --------------------------------------------------------------\n";
        echo "  This console provides a set of commands to help you manage your Simple MVC application.\n";
        echo "  To get started, type 'help' to see a list of available commands.\n";
        echo "  Below is a list of available commands:\n\r";
        echo "  --------------------------------------------------------------\n\r";
        echo "  help, -h               - Show this help message\n";
        echo "  console:setup          - Setup console file\n";
        echo "  app:setup              - Setup app, load migration system\n";
        echo "  --------------------------------------------------------------\n\r";
        echo "  make:controller [name] - Create a new controller\n";
        echo "  make:model [name]      - Create a new model\n";
        echo "  make:view [name]       - Create a new view\n";
        echo "  ---------------------------------------------------------------\n\r";
        echo "  migrations:create           - Generate migration files\n";
        echo "  migrations:run [command]    - Run migration command 'run' or 'rollback'\n";
        echo "  ---------------------------------------------------------------\n\r";

        // show the commands registered in the CommandManager
        // echo "  Additional commands can be registered in the CommandManager.\n";
        // echo "  To register a new command, create a class in the 'App\\Commands' namespace\n";
        // echo "  and implement the 'execute' method. The command will be automatically registered.\n";
        // echo "  ---------------------------------------------------------------\n\r";
        // echo "  For more information on how to use the console, visit the documentation at:\n";

        // scan the commands directory for additional commands
        // $commandsDir = BASE_PATH_IN_COMMANDS . '/commands';
        // if (is_dir($commandsDir)) {
        //     $commandFiles = glob($commandsDir . '/*.php');
        //     if (!empty($commandFiles)) {
        //         echo "  Available commands:\n";
        //         foreach ($commandFiles as $file) {
        //             $commandName = basename($file, '.php');
        //             echo "    - " . strtolower(str_replace('Command', '', $commandName)) . "\n";
        //         }
        //     } else {
        //         echo "  No additional commands found in the 'commands' directory.\n";
        //     }
        // } else {
        //     echo "  The 'commands' directory does not exist.\n";
        // }
    }
}