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

        $viewName = ucfirst($arguments[0]);
        $path = __DIR__ . '/../../../app/views/' . $viewName . '.php';

        if (file_exists($path)) {
            echo "Model already exists!\n";
            return;
        }

        $content = "{% extends 'layout.html.twig' %}\n\n{% block title %}Blank Page{% endblock %}\n\n{% block content %}\n    <h1>Blank Page</h1>\n    <p>This is a blank page.</p>\n{% endblock %}\n";

        file_put_contents($path, $content);
        echo "View $viewName created successfully.\n";
    }
}