<?php

namespace Core\Console\Commands;

class HelpCommand
{
    public function execute(array $arguments)
    {
        echo "\n\r  Available commands:\n";
        echo "  --------------------------------------------------------------\n";
        echo "  help, -h               - Show this help message\n";
        echo "  console:setup          - Setup console file\n";
        echo "  app:setup              - Setup app, load migration system\n";
        echo "  --------------------------------------------------------------\n";
        echo "  make:controller [name] - Create a new controller\n";
        echo "  make:model [name]      - Create a new model\n";
        echo "  make:view [name]       - Create a new view\n";
        echo "  ---------------------------------------------------------------\n";
        echo "  migrations:create      - Generate migration files\n";
        echo "  migrations:run         - Run migrations\n";
        echo "  ---------------------------------------------------------------\n";
    }
}