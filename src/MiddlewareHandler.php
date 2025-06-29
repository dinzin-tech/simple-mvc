<?php
namespace Core;

use Core\Http\Request;
use Core\Http\Response;
use App\Middlewares\MiddlewareInterface;

class MiddlewareHandler
{
    protected array $middlewares;

    public function __construct(array $middlewareClasses)
    {
        $this->middlewares = $middlewareClasses;
    }

    /**
     * Handles the middleware chain execution.
     *
     * @param Request $request
     * @param callable $next
     * @return Response
     */
    public function handle(Request $request, callable $next): Response
    {
        $queue = $this->middlewares;

        $runner = function (Request $request) use (&$queue, $next, &$runner): Response {
            if (empty($queue)) {
                return $next($request); // No more middleware
            }

            $middlewareClass = array_shift($queue);
            $fqcn = "App\\Middlewares\\{$middlewareClass}";

            if (!class_exists($fqcn)) {
                return (new Response())
                    ->setStatusCode(500)
                    ->setContent("Middleware not found: {$fqcn}");
            }

            $middleware = new $fqcn();

            if (!($middleware instanceof MiddlewareInterface)) {
                return (new Response())
                    ->setStatusCode(500)
                    ->setContent("Invalid middleware: {$fqcn} must implement MiddlewareInterface");
            }

            return $middleware->handle($request, function (Request $req) use ($runner) {
                return $runner($req); // continue with the next middleware
            });
        };

        return $runner($request);
    }
}
