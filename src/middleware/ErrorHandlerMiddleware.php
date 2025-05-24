<?php
declare(strict_types=1);

namespace Core\Middleware;

use Core\Http\Request;
use Core\Http\Response;

class ErrorHandlerMiddleware implements MiddlewareInterface {
    public function process(Request $request, Response $response): void {
        try {
            // Register global error handler
            set_exception_handler([$this, 'handleException']);
        } catch (\Throwable $e) {
            $response->setStatusCode(500)
                     ->setContent("Internal Server Error: " . $e->getMessage())
                     ->send();
        }
    }

    public function handleException(\Throwable $e): void {
        // Log the error or render a custom error page
    }
}