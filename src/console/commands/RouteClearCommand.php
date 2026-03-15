<?php

namespace Core\Console\Commands;

class RouteClearCommand
{
    public function execute(array $arguments)
    {
        echo "Clearing route cache...\n";

        $cacheFile = BASE_PATH_IN_COMMANDS . '/cache/routes.php';

        if (file_exists($cacheFile)) {
            unlink($cacheFile);
            echo "Route cache cleared successfully.\n";
        } else {
            echo "No route cache found.\n";
        }
    }
}
