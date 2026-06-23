<?php

if (!defined('ABSPATH')) {
    exit;
}

class FormNova_Validator
{
    public static function required($value)
    {
        if (is_array($value)) {
            return !empty($value);
        }

        return strlen(trim((string) $value)) > 0;
    }

    public static function email($value)
    {
        return is_email($value);
    }

    public static function number($value)
    {
        return is_numeric($value);
    }

    public static function checkbox_required($value)
    {
        return !empty($value);
    }

    public static function select_required($value)
    {
        return !empty($value);
    }

    public static function field_type($type)
    {
        $allowed = [
            'text',
            'email',
            'number',
            'textarea',
            'select',
            'radio',
            'checkbox',
            'tel',
            'date',
            'url',
            'file'
        ];

        return in_array(
            $type,
            $allowed,
            true
        );
    }

    public static function text($value)
    {
        return is_string($value) && strlen(trim($value)) > 0;
    }

    public static function tel($value)
    {
        return preg_match('/^[0-9+\-\s()]{7,20}$/', $value);
    }

    public static function textarea($value)
    {
        return is_string($value);
    }

    public static function url($value)
    {
        return filter_var($value, FILTER_VALIDATE_URL);
    }

    public static function date($value)
    {
        $d = DateTime::createFromFormat('Y-m-d', $value);
        return $d && $d->format('Y-m-d') === $value;
    }
}