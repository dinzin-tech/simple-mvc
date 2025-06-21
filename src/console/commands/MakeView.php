<?php

namespace Core\Console\Commands;

class MakeView
{
    public function execute(array $arguments)
    {
        if (empty($arguments[0])) {
            echo "Please provide a name for the view.\n";
            return;
        }

        $viewName = $arguments[0];
        $path = realpath(BASE_PATH_IN_COMMANDS . "/app/views");
        
        // Ensure the directory exists, or create it if it doesn't
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }

        $filePath = $path . DIRECTORY_SEPARATOR . $viewName . '.html.twig';

        // Handle subdirectories like auth/login
        $directory = dirname($filePath);

        if (!is_dir($directory)) {
            mkdir($directory, 0777, true); // Recursively create directories
        }

        if (file_exists($filePath)) {
            echo "View $viewName already exists!\n";
            return;
        }

        $content = "{% extends 'layout.html.twig' %}\n\n{% block title %}Blank Page{% endblock %}\n\n{% block content %}\n    <h1>Blank Page</h1>\n    <p>This is a blank page.</p>\n{% endblock %}\n";

        file_put_contents($filePath, $content);
        echo "View $viewName created successfully.\n";
    }
}