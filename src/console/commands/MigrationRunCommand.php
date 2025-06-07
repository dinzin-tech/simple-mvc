<?php

namespace Core\Console\Commands;

class MigrationRunCommand
{
    public function execute()
    {
        echo "-- Running migrations...\n";
        echo "-- This may take a while, please wait --\n";
        sleep(1);
        \Core\Migration::run();
        echo "-- Migrations executed successfully!\n";
        echo "Done!\n";
        echo "-- You can now check your database for the applied changes.\n";
    }
}