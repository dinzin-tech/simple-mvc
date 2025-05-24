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
     * For example: {{ route('home') }}
     *
     * @param string $name
     * @param array $params
     * @return string
     */
    public function generateRoute($name, $params = []): string
    {
        return $this->router->getRouteByName($name/*, $params*/);
    }
}