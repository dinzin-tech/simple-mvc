<?php
declare(strict_types=1);

namespace Core\Http;

class Response {
    private $statusCode = 200;
    private $headers = [];
    private $content = '';
    private $sent = false;

    // Constructor to accept content and status code
    public function __construct(string $content = '', int $statusCode = 200, array $headers = []) {
        $this->content = $content;
        $this->statusCode = $statusCode;
        $this->headers = $headers;
    }

    public function setStatusCode(int $statusCode): self {
        $this->statusCode = $statusCode;
        return $this;
    }

    public function addHeader(string $name, string $value): self {
        $this->headers[$name] = $value;
        return $this;
    }

    public function setContent(string $content): self {
        $this->content = $content;
        return $this;
    }

    public function send(): void {
        if ($this->sent) {
            throw new \RuntimeException('Response has already been sent.');
        }

        http_response_code($this->statusCode);
        foreach ($this->headers as $name => $value) {
            header("$name: $value");
        }
        echo $this->content;

        $this->sent = true;
    }

    public function isSent(): bool {
        return $this->sent;
    }

    // Convenience method for JSON responses
    public function json($data, int $statusCode = 200): self {
        $this->setContent(json_encode($data));
        $this->setStatusCode($statusCode);
        $this->addHeader('Content-Type', 'application/json');
        return $this;
    }
    
}