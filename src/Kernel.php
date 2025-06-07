<?php
declare(strict_types=1);

namespace Core;

use Core\Http\Request;
use Core\Http\Response;
use Core\Router;
use Core\Debug;
use Core\Session;

class Kernel
{
    protected $router;
    protected $debug;

    /**
     * Boot the application.
     */
    public function boot(): void
    {
        // Start the session
        Session::start();
        
        // Initialize the router
        $this->router = new Router();
        $this->debug = new Debug($this->router);

        // define the configuration
        define('BASE_PATH', dirname(__DIR__, 4));

        // Load the routes
        $this->router->loadRoutes();

        // Set error reporting and display based on the environment
        if($_ENV['APP_ENV'] === 'dev') {
            error_reporting(E_ALL);
            ini_set('display_errors', '1');
        }
        
    }

    /**
     * Handle the incoming request and return a response.
     *
     * @param Request $request
     * @return Response
     */
    public function handle(Request $request)
    {
        // Dispatch the request to the router
        $this->router->dispatch($request->getUri());

        // Return the response from the router
        return $this->router->response;
    }

    /**
     * Terminate the request/response lifecycle.
     *
     * @param Request $request
     * @param Response $response
     */
    public function terminate(Request $request, Response $response): void
    {
        // Send the response to the client
        $response->send();

        if ($_ENV['DEBUG_MODE'] == 'true') {
            $this->debug->render();
        } // Render debug information at the end
    }

    /**
     * Get the router instance.
     *
     * @return Router
     */
    public function getRouter(): Router
    {
        return $this->router;
    }
}