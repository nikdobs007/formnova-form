<?php

if (!defined('ABSPATH')) {
    exit;
}

class FormNova_Sanitizer
{
    public static function text($value)
    {
        return sanitize_text_field(
            wp_unslash($value)
        );
    }

    public static function key($value)
    {
        return sanitize_key(
            wp_unslash($value)
        );
    }

    public static function textarea($value)
    {
        return sanitize_textarea_field(
            wp_unslash($value)
        );
    }

    public static function email($value)
    {
        return sanitize_email(wp_unslash($value));
    }

    public static function url($value)
    {
        return esc_url_raw(wp_unslash($value));
    }

    public static function number($value)
    {
        return intval($value);
    }
}