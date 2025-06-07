<?php

namespace Core\Console\Commands;

class ConsoleSetupCommand
{
    public function execute()
    {

        echo "Setting up the console environment...\n";
        sleep(1);
        echo "Loading necessary components...\n";
        sleep(1);

        $console_path = BASE_PATH_IN_COMMANDS.'bin/console';

$content = `#!/usr/bin/env php
<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Core\Console\CommandManager;

// load environment variables
\$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
\$dotenv->load();

// Check if a command is passed
if (\$argc < 2) {
    echo "No command provided. Use 'php console.php help' for a list of commands.\n";
    exit(1);
}

\$commandManager = new CommandManager();
\$commandManager->run(\$argv);
`;

        // check if bin/console exist
        if (!file_exists($console_path)) {
            echo "bin/console does not exist. Please run composer install.\n";

            // create bin/console
            $file = fopen($console_path, 'w');
            
            // write content to bin/console
            fwrite($file, $content);

            fclose($file);

            // make bin/console executable
            chmod($console_path, 0755);

            echo "bin/console created. Please run composer install.\n";

            return;
        }
        else {
            echo "bin/console exists. Updating...\n";
            sleep(1);

            // update bin/console
            $file = fopen($console_path, 'w');

            // remove existing content and write new content
            ftruncate($file, 0);
            fwrite($file, $content);

            fclose($file);

            // make bin/console executable
            chmod($console_path, 0755);

            echo "bin/console updated.\n";

            return;
        }
        


    }
}