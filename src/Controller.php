<?php
declare(strict_types=1);

namespace Core;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

use Twig\TwigFunction;
use Core\Http\Request;
use Core\Http\Response;
use Core\Cache;

class Controller {
    protected $twig;
    protected $router;
    protected $cache;

    public function __construct() {
        // Initialize Twig
        $loader = new FilesystemLoader(BASE_PATH . '/app/views'); // Path to your templates
        $this->twig = new Environment($loader, [
            'cache' => BASE_PATH . '/storage/cache/twig', // Path to cache directory
            'debug' => true, // Enable debug mode (optional)
        ]);

        // Register custom Twig function
        // $this->twig->addFunction(new TwigFunction('assets', [$this, 'assets']));

        $twigExtension = new TwigExtension(); 

        // Register custom Twig extension
        // $this->twig->addExtension(new TwigExtension());
        $this->twig->addExtension($twigExtension);

        // Add debug extension (optional)
        if ($this->twig->isDebug()) {
            $this->twig->addExtension(new \Twig\Extension\DebugExtension());
        }

        // cache instance
        $this->cache = new Cache(BASE_PATH . '/storage/cache');
    }

    /**
     * Render a view using Twig.
     *
     * @param string $view The template file (e.g., 'home/index.twig')
     * @param array $data Data to pass to the template
     * @return string The rendered template
     */
    protected function render(string $view, array $data = []) {
        return new Response($this->twig->render($view, $data));
        // exit;
    }

    /**
     * Redirect to a URL.
     *
     * @param string $url The URL to redirect to
     * @param int $status The HTTP status code (default: 302)
     * @return void
     */
    public function redirect(string $url, int $status = 302): void {
        header('Location: ' . $url, true, $status);
        exit;
    }

    /**
     * Load CSS or JS assets.
     *
     * @param string $type
     * @param string $path
     * @return string
     */
    // public function asset($path)
    // {
    //     $baseUrl = '/assets/';
    //     return $baseUrl . $path;
    // }
}