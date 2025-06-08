<?php

namespace Core;

use Random\RandomException;
use Core\Session;
use Core\Http\Request;

class Form
{
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
            'file_upload_dir' => $_ENV['FILE_UPLOAD_DIR'] ?: 'uploads',
            'file_allowed_types' => explode(',', $_ENV['FILE_ALLOWED_TYPES'] ?: 'jpg,jpeg,png,gif'),
            'file_max_size' => (int)($_ENV['FILE_MAX_SIZE'] ?: 2 * 1024 * 1024) // Default 2MB
        ];

        $this->csrfToken = Session::get('csrf_token') ?? $this->generateCSRFToken();
        Session::set('csrf_token', $this->csrfToken);
    }

    /**
     * @throws RandomException
     */
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

    public function addField(string $type, string $name, $value = null, array $options = []): void
    {
        $this->fields[] = [
            'type'    => $type,
            'name'    => $name,
            'value'   => $value,
            'options' => $options
        ];
    }

    public function add(string $type, string $name, $value = null, array $options = []): void
    {
        $this->addField($type, $name, $value, $options);
    }

    public function render(): string
    {
        $html = '<form method="' . htmlspecialchars($this->method) 
              . '" action="' . htmlspecialchars($this->action) . '"';

        // Add enctype if file input exists
        foreach ($this->fields as $field) {
            if ($field['type'] === 'file') {
                $html .= ' enctype="multipart/form-data"';
                break;
            }
        }

        $html .= '>';
        $html .= '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($this->csrfToken) . '">';

        foreach ($this->fields as $field) {
            $html .= $this->renderField($field);
        }

        $html .= '<button type="submit">' . htmlspecialchars($this->submitButtonValue) . '</button>';
        $html .= '</form>';

        return $html;
    }

    private function renderField(array $field): string
    {
        $type = $field['type'];
        $name = $field['name'];
        $value = htmlspecialchars((string)$field['value']);
        $options = $field['options'];
        $label = $options['label'] ?? '';
        $attrs = $this->buildAttributes($options);

        $html = '';

        if ($label && !in_array($type, ['checkbox', 'radio'])) {
            $html .= '<label for="' . htmlspecialchars($name) . '">' . htmlspecialchars($label) . '</label>';
        }

        switch ($type) {
            case 'textarea':
                $html .= '<textarea name="' . htmlspecialchars($name) . '" id="' . htmlspecialchars($name) 
                      . '"' . $attrs . '>' . $value . '</textarea>';
                break;
            
            case 'select':
                $html .= '<select name="' . htmlspecialchars($name) . '" id="' . htmlspecialchars($name) 
                      . '"' . $attrs . '>';
                foreach ($options['options'] ?? [] as $optValue => $optLabel) {
                    $selected = $optValue == $field['value'] ? ' selected' : '';
                    $html .= '<option value="' . htmlspecialchars($optValue) . '"' . $selected . '>'
                          . htmlspecialchars($optLabel) . '</option>';
                }
                $html .= '</select>';
                break;
            
            case 'checkbox':
            case 'radio':
                if ($label) {
                    $html = '<label>';
                }
                $html .= '<input type="' . htmlspecialchars($type) . '" name="' . htmlspecialchars($name)
                      . '" id="' . htmlspecialchars($name) . '" value="' . $value . '"'
                      . ($field['value'] ? ' checked' : '') . $attrs . '>';
                if ($label) {
                    $html .= htmlspecialchars($label) . '</label>';
                }
                break;
            
            case 'file':
                $html .= '<input type="file" name="' . htmlspecialchars($name) . '" id="' . htmlspecialchars($name) 
                      . '"' . $attrs . '>';
                break;
            
            default:
                $html .= '<input type="' . htmlspecialchars($type) . '" name="' . htmlspecialchars($name) 
                      . '" id="' . htmlspecialchars($name) . '" value="' . $value . '"' . $attrs . '>';
        }

        return $html;
    }

    private function buildAttributes(array $options): string
    {
        $attrs = '';
        $attributes = $options['attributes'] ?? [];

        // Handle required attribute
        if (!empty($options['required'])) {
            $attributes['required'] = true;
        }

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
            foreach ($this->fields as $index => $field) {
                if ($field['type'] === 'file') {
                    $this->handleFileUpload($index, $field['name']);
                } else {
                    $this->fields[$index]['value'] = $request->get($field['name']);
                }
            }
        }
    }

    private function handleFileUpload(int $index, string $fieldName): void
    {
        if (empty($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] === UPLOAD_ERR_NO_FILE) {
            return;
        }

        $file = $_FILES[$fieldName];
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        // Validate file type
        if (!in_array($extension, $this->config['file_allowed_types'])) {
            $this->addError('Invalid file type. Allowed types: ' 
                . implode(', ', $this->config['file_allowed_types']));
            return;
        }

        // Validate file size
        if ($file['size'] > $this->config['file_max_size']) {
            $this->addError('File size exceeds maximum allowed size of ' 
                . $this->formatBytes($this->config['file_max_size']));
            return;
        }

        // Create upload directory if needed
        if (!is_dir($this->config['file_upload_dir'])) {
            mkdir($this->config['file_upload_dir'], 0755, true);
        }

        // Generate unique filename
        $filename = bin2hex(random_bytes(16)) . '.' . $extension;
        $targetPath = rtrim($this->config['file_upload_dir'], '/') . '/' . $filename;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            $this->fields[$index]['value'] = $targetPath;
        } else {
            $this->addError('Failed to upload file.');
        }
    }

    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    public function validate(Request $request): bool
    {
        $this->errors = [];

        if (!$this->isSubmitted()) {
            return false;
        }

        foreach ($this->fields as $field) {
            $value = $field['value'];
            $options = $field['options'];

            // Required validation
            if (!empty($options['required']) && empty($value)) {
                $this->addError(ucfirst($field['name']) . ' is required.');
            }

            // Add more validations as needed (email, min/max length, etc.)
        }

        // CSRF validation
        if (!$this->validateCSRFToken($request->get('csrf_token'))) {
            $this->addError('Invalid CSRF token.');
        }

        return empty($this->errors);
    }

    public function isSubmitted(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    private function validateCSRFToken(string $token): bool
    {
        if (empty($token)) {
            throw new \RuntimeException('CSRF token is missing.');
        }
        return hash_equals($this->csrfToken, $token ?? '');
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function addError(string $message): void
    {
        $this->errors[] = $message;
    }

    public function getFormData(): array
    {
        $data = [];
        foreach ($this->fields as $field) {
            $data[$field['name']] = $field['value'];
        }
        return $data;
    }
}