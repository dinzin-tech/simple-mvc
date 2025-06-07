<?php

namespace Core\Console\Commands;

class MigrationGenerateCommand
{
    public function execute()
    {
        echo "-- Generating migration files...\n";
        echo "-- This may take a while, please wait --\n";
        sleep(1);
        \Core\Migration::generateMigrations();
        echo "-- Migration files generated successfully!\n";
        echo "-- You can now run 'php bin/console migrations:run' to execute the migrations.\n";
        echo "Done!\n";
    }
}