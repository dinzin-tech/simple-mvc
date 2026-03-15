<?php

namespace Core\Console\Commands;

use Core\Router;

class RouteCacheCommand
{
    public function execute(array $arguments)
    {
        echo "Generating route cache...\n";

        // Instantiate the router and scan controllers to build the route list
        $router = new Router();
        $router->scanControllers();
        
        // Expose $router->routes using a temporary hack or by adding a getter
        // For compatibility with any changes, let's reflect into the private property if necessary, 
        // but we'll modify Router to add getRoutes() instead.
        if (method_exists($router, 'getRoutes')) {
            $routes = $router->getRoutes();
        } else {
            // Fallback for older core if getRoutes isn't available yet
            $reflection = new \ReflectionClass($router);
            $property = $reflection->getProperty('routes');
            $property->setAccessible(true);
            $routes = $property->getValue($router);
        }
        
        if (empty($routes)) {
            echo "No routes found. Cache not generated.\n";
            return;
        }

        $cacheDir = BASE_PATH_IN_COMMANDS . '/cache';
        $cacheFile = $cacheDir . '/routes.php';

        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0777, true);
        }

        // Export the routes array into a PHP file
        $content = "<?php\n\n// Auto-generated route cache.\n// Do not modify this file manually.\n\nreturn " . var_export($routes, true) . ";\n";
        
        file_put_contents($cacheFile, $content);
        
        echo "Route cache generated successfully at {$cacheFile}\n";
    }
}
