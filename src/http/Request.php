<?php
declare(strict_types=1);

namespace Core\Http;

class Request {
    private $method;
    private $uri;
    private $queryParams;
    private $postData;

    public function __construct() {
        $this->method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $this->uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $this->queryParams = $_GET;
        $this->postData = $_POST;
    }

    // Add new method for testing
    public function setTestUri(string $uri): void {
        $this->uri = $uri;
    }

    public function getMethod(): string {
        return $this->method;
    }

    public function getUri(): string {
        return $this->uri;
    }

    public function getQueryParams(): array {
        return $this->queryParams;
    }

    public function getPostData(): array {
        return $this->postData;
    }

    public function get(string $key, $default = null) {
        return $this->queryParams[$key] ?? $this->postData[$key] ?? $default;
    }

    public function isHtmx(): bool
    {
        return isset($_SERVER['HTTP_HX_REQUEST']) && $_SERVER['HTTP_HX_REQUEST'] === 'true';
    }
    
}