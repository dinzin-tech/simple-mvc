<?php

namespace Core;

use Core\Field;
use Core\Session;
use Core\Http\Request;
use Random\RandomException;

class Form
{
    /** @var array<string, Field> */
    private array $fields = [];
    private string $csrfToken;
    private string $method = 'POST';
    private string $action = '';
    private string $submitButtonValue = 'Submit';
    private array $errors = [];
    private array $config;

    /**
     * @throws RandomException
     */
    public function __construct()
    {
        $this->config = [
            'file_upload_dir' => $_ENV['FILE_UPLOAD_DIR'] ?? 'uploads',
            'file_allowed_types' => explode(',', $_ENV['FILE_ALLOWED_TYPES'] ?? 'jpg,jpeg,png,gif'),
            'file_max_size' => (int)($_ENV['FILE_MAX_SIZE'] ?? 2 * 1024 * 1024)
        ];

        $this->csrfToken = Session::get('csrf_token') ?? $this->generateCSRFToken();
        Session::set('csrf_token', $this->csrfToken);
    }

    private function generateCSRFToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    public function setMethod(string $method): void
    {
        $this->method = strtoupper($method);
    }

    public function setAction(string $action): void
    {
        $this->action = $action;
    }

    public function setSubmitButtonValue(string $value): void
    {
        $this->submitButtonValue = $value;
    }

    public function addField(string $type, string $name, mixed $value = null, array $options = []): void
    {
        $this->fields[$name] = new Field($type, $name, $value, $options);
    }

    public function add(string $type, string $name, mixed $value = null, array $options = []): void
    {
        $this->addField($type, $name, $value, $options);
    }

    public function getFields(): array
    {
        return $this->fields;
    }

    public function getField(string $name): ?Field
    {
        return $this->fields[$name] ?? null;
    }

    public function getCsrfToken(): string
    {
        return $this->csrfToken;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function getSubmitButtonValue(): string
    {
        return $this->submitButtonValue;
    }

    public function render(array $formAttributes = [], array $submitAttributes = []): string
    {
        $formAttrStr = $this->buildAttributes($formAttributes);
        $enctype = '';

        foreach ($this->fields as $field) {
            if ($field->type === 'file') {
                $enctype = ' enctype="multipart/form-data"';
                break;
            }
        }

        $html = "<form method=\"{$this->method}\" action=\"{$this->action}\"$formAttrStr$enctype>";
        $html .= '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($this->csrfToken) . '">';

        foreach ($this->fields as $field) {
            $html .= $field->render();
        }

        $submitAttrStr = $this->buildAttributes($submitAttributes);
        $html .= '<button type="submit"' . $submitAttrStr . '>' . htmlspecialchars($this->submitButtonValue) . '</button>';
        $html .= '</form>';

        return $html;
    }

    private function buildAttributes(array $attributes = []): string
    {
        $attrs = '';
        foreach ($attributes as $attr => $val) {
            if (is_bool($val)) {
                if ($val) $attrs .= ' ' . htmlspecialchars($attr);
            } else {
                $attrs .= ' ' . htmlspecialchars($attr) . '="' . htmlspecialchars((string)$val) . '"';
            }
        }
        return $attrs;
    }

    public function handle(Request $request): void
    {
        if ($request->getMethod() === 'POST') {
            foreach ($this->fields as $name => $field) {
                if ($field->type === 'file') {
                    $this->handleFileUpload($name);
                } else {
                    $this->fields[$name]->value = $request->get($name);
                }
            }
        }
    }

    private function handleFileUpload(string $fieldName): void
    {
        if (empty($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] === UPLOAD_ERR_NO_FILE) {
            return;
        }

        $file = $_FILES[$fieldName];
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($extension, $this->config['file_allowed_types'])) {
            $this->addError("Invalid file type. Allowed: " . implode(', ', $this->config['file_allowed_types']));
            return;
        }

        if ($file['size'] > $this->config['file_max_size']) {
            $this->addError('File too large. Max: ' . $this->formatBytes($this->config['file_max_size']));
            return;
        }

        if (!is_dir($this->config['file_upload_dir'])) {
            mkdir($this->config['file_upload_dir'], 0755, true);
        }

        $filename = bin2hex(random_bytes(16)) . '.' . $extension;
        $targetPath = rtrim($this->config['file_upload_dir'], '/') . '/' . $filename;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            $this->fields[$fieldName]->value = $targetPath;
        } else {
            $this->addError('File upload failed.');
        }
    }

    public function validate(Request $request): bool
    {
        $this->errors = [];

        if (!$this->isSubmitted()) {
            return false;
        }

        foreach ($this->fields as $field) {
            $value = $field->value;
            $options = $field->options;

            if (!empty($options['required']) && empty($value)) {
                $this->addError(ucfirst($field->name) . ' is required.');
            }
        }

        if (!$this->validateCSRFToken($request->get('csrf_token'))) {
            $this->addError('Invalid CSRF token.');
        }

        return empty($this->errors);
    }

    private function validateCSRFToken(?string $token): bool
    {
        if (empty($token)) {
            throw new \RuntimeException('CSRF token is missing.');
        }

        return hash_equals($this->csrfToken, $token);
    }

    public function isSubmitted(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    public function addError(string $message): void
    {
        $this->errors[] = $message;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getFormData(): array
    {
        $data = [];
        foreach ($this->fields as $field) {
            $data[$field->name] = $field->value;
        }
        return $data;
    }
}
