<?php
namespace Core;

class MiddlewareResolver
{
    protected array $middlewareMap = [];

    public function __construct(string $filePath)
    {
        if (file_exists($filePath)) {
            $this->middlewareMap = \Symfony\Component\Yaml\Yaml::parseFile($filePath);
        }
    }

    /**
     * Get middleware array for a given route (exact or wildcard match).
     */
    public function resolve(string $routePath): array
    {
        // Exact match first
        if (isset($this->middlewareMap[$routePath])) {
            return (array) $this->middlewareMap[$routePath];
        }

        // Check wildcard patterns
        foreach ($this->middlewareMap as $pattern => $middleware) {
            if (str_ends_with($pattern, '/*')) {
                $prefix = rtrim($pattern, '/*');
                if (str_starts_with($routePath, $prefix)) {
                    return (array) $middleware;
                }
            }
        }

        return [];
    }
}
