<?php

namespace Core\Console\Commands;

class MigrationRunCommand
{
    public function execute(array $arguments)
    {
        if (empty($arguments[0])) {
            echo "Please provide a valid command.\n";
            echo "Available commands:\n";
            echo "  - 'migrations run' to execute migrations.\n";
            echo "  - 'migrations rollback' to rollback migrations.\n";
            return;
        }

        $command = strtolower($arguments[0]);
        
        if ($command === 'rollback') {

            echo "-- Rolling back migrations...\n";
            echo "-- This may take a while, please wait --\n";
            sleep(1);
            \Core\Migration::rollback();
            echo "-- Migrations rolled back successfully!\n";
            echo "Done!\n";
            echo "-- You can now check your database for the reverted changes.\n";

        } elseif ($command === 'run') {
            
            echo "-- Running migrations...\n";
            echo "-- This may take a while, please wait --\n";
            sleep(1);
            \Core\Migration::run();
            echo "-- Migrations executed successfully!\n";
            echo "Done!\n";
            echo "-- You can now check your database for the applied changes.\n";

        } else {
            echo "Invalid command. Use 'migrations run' or 'migrations rollback'.\n";
        }

        // echo "-- Running migrations...\n";
        // echo "-- This may take a while, please wait --\n";
        // sleep(1);
        // \Core\Migration::run();
        // echo "-- Migrations executed successfully!\n";
        // echo "Done!\n";
        // echo "-- You can now check your database for the applied changes.\n";
    }
}