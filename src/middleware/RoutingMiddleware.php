<?php
declare(strict_types=1);

namespace Core\Middleware;

use Core\Http\Request;
use Core\Http\Response;
use Core\Router;

class RoutingMiddleware implements MiddlewareInterface {
    private $router;

    public function __construct(Router $router) {
        $this->router = $router;
    }

    public function process(Request $request, Response $response): void {
        // Dispatch the route
        $this->router->dispatch($request, $response);

        // Send a 404 if no response was generated
        if (!$response->isSent()) {
            $response->setStatusCode(404)
                     ->setContent("Page not found")
                     ->send();
        }
    }
}