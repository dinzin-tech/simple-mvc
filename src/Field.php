<?php

namespace Core;

class Field
{
    public string $type;
    public string $name;
    public mixed $value;
    public array $options;

    public function __construct(string $type, string $name, mixed $value = null, array $options = [])
    {
        $this->type = $type;
        $this->name = $name;
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
