<?php

namespace Core;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class TwigExtension extends AbstractExtension
{
    protected $router;

    public function __construct()
    {
        $this->router = new Router();
        $this->router->loadRoutes();
    }

    /**
     * Registers custom functions to use in Twig.
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('assets', [$this, 'assets']),
            new TwigFunction('route', [$this, 'generateRoute']),
        ];
    }

    /**
     * Generate asset URL.
     * For example: {{ asset('css/style.css') }}
     *
     * @param string $path
     * @return string
     */
    public function assets($path): string
    {
        // Assuming all assets are in the "public" folder
        return '/public/assets/' . $path;
    }

    /**
     * Generate URL for a named route.
     * For example: {{ route('user_profile', {'id': 42, 'tab': 'settings'}) }}
     *
     * @param string $name
     * @param array $params
     * @return string
     */
    public function generateRoute(string $name, array $params = []): string
    {
        $path = $this->router->getRouteByName($name);

        if ($path === null) {
            return '#'; // fallback if route not found
        }

        // Find all placeholders in the route (e.g., {id}, {slug})
        preg_match_all('/{(\w+)}/', $path, $matches);
        $routeParams = $matches[1] ?? [];

        // Replace each placeholder with provided value or leave as-is
        foreach ($routeParams as $paramName) {
            if (isset($params[$paramName])) {
                $path = str_replace("{" . $paramName . "}", urlencode((string) $params[$paramName]), $path);
                unset($params[$paramName]); // Remove used param
            } else {
                // Optional: If parameter is missing, you can choose to leave it or remove it
                // e.g., /user/{id} → /user/ if id is not provided
                $path = str_replace("{" . $paramName . "}", '', $path);
            }
        }

        // Append any remaining parameters as query string
        if (!empty($params)) {
            $path .= '?' . http_build_query($params);
        }

        return $path;
    }
}