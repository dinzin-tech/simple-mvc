<?php

namespace Core\Console\Commands;

class AppSetupCommand
{
    public function execute()
    {

        $db = \Core\Database::getInstance();

        echo "-- Checking setup\n";

        // You can implement a check for new migrations here
        // check migrations table exists
        $migrationsTableExists = $db->query("SHOW TABLES LIKE 'migrations'")->fetch();
        sleep(1);
        echo "-- Checking migrations table...\n";
        sleep(1);

        if ($migrationsTableExists) {
            echo "-- Migrations table found.\n";

        } else {
            echo "-- Migrations table not found.\n";
            echo "-- Creating migrations table...\n";
            sleep(1);
            // If the migrations table does not exist, we can create it
            $db->query("CREATE TABLE IF NOT EXISTS migrations (
                id INT AUTO_INCREMENT PRIMARY KEY,
                filename VARCHAR(255) NOT NULL,
                ran_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
            );")->execute();
            echo "-- Migrations table created.\n";
            sleep(1);
            // $migrationsTableExists = true; // Set to true since we just created it
        }


        // first check if the migrations directory exists
        if (!is_dir('migrations')) {
            echo "-- No migrations directory found.\n";
            echo "-- Creating migrations directory...\n";
            sleep(1);
            // Create migrations directory
            mkdir('migrations', 0755, true);
            echo "-- Migrations directory created.\n";
            sleep(1); // Sleep for 0.5 seconds to ensure the directory is created
        }

        echo "-- Checking for migration files...\n";
        // then check if there are any migrations files
        $files = glob('migrations/*.php');
        sleep(1); // Sleep for 0.1 seconds to ensure the files are loaded
    
        echo "-- Found " . count($files) . " migration files.\n";
        sleep(1);
        echo "-- run `php bin/console migrations:create` to generate the migrations.\n";
        sleep(1);
        echo "-- Environment setup complete.\n";

    }
}