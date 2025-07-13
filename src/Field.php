<?php

namespace Core;

class Field
{
    /**
     * @var string The name of the field.
     */
    public string $name;

    /**
     * @var string The type of the field (e.g., text, textarea, select, checkbox, radio, file).
     */
    public string $type;

    /**
     * @var mixed The value of the field.
     */
    public mixed $value;

    /**
     * @var array Additional options for the field, such as label, attributes, and options for select fields.
     */
    public array $options;

    public function __construct(string $name, string $type, mixed $value = null, array $options = [])
    {
        $this->name = $name;
        $this->type = $type;
        $this->value = $value;
        $this->options = $options;
    }

    public function render(): string
    {
        $type = $this->type;
        $name = $this->name;
        $value = htmlspecialchars((string)($this->value ?? ''));
        $options = $this->options;
        $label = $options['label'] ?? '';
        $labelAttrs = $this->buildAttributes($options['label_attributes'] ?? []);
        $inputAttrs = $this->buildAttributes($options['attributes'] ?? [], !empty($options['required']));

        $html = '';

        if ($label && !in_array($type, ['checkbox', 'radio'])) {
            $html .= "<label test=\"true\" for=\"$name\" $labelAttrs>" . htmlspecialchars($label) . '</label>';
        }

        switch ($type) {
            case 'textarea':
                $html .= "<textarea name=\"$name\" id=\"$name\"$inputAttrs>$value</textarea>";
                break;

            case 'select':
                $html .= "<select name=\"$name\" id=\"$name\"$inputAttrs>";
                foreach ($options['options'] ?? [] as $optValue => $optLabel) {
                    $selected = $optValue == $this->value ? ' selected' : '';
                    $html .= '<option value="' . htmlspecialchars($optValue) . '"' . $selected . '>'
                          . htmlspecialchars($optLabel) . '</option>';
                }
                $html .= '</select>';
                break;

            case 'checkbox':
            case 'radio':
                if ($label) $html .= "<label$labelAttrs>";
                $checked = !empty($this->value) ? ' checked' : '';
                $html .= "<input type=\"$type\" name=\"$name\" id=\"$name\" value=\"$value\" $checked$inputAttrs>";
                if ($label) $html .= htmlspecialchars($label) . '</label>';
                break;

            case 'file':
                $html .= "<input type=\"file\" name=\"$name\" id=\"$name\" $inputAttrs>";
                break;

            default:
                $html .= "<input type=\"$type\" name=\"$name\" id=\"$name\" value=\"$value\" $inputAttrs>";
        }

        return $html;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Build HTML attributes from an associative array.
     *
     * @param array $attributes
     * @param bool $required
     * @return string
     */
    private function buildAttributes(array $attributes = [], bool $required = false): string
    {
        if ($required) {
            $attributes['required'] = true;
        }

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
}
