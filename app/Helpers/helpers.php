<?php

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('formnova_request')) {

    function formnova_request($key, $method = 'request')
    {
        $source = [];

        switch (strtolower($method)) {
            case 'get':
                // phpcs:ignore WordPress.Security.NonceVerification.Recommended
                $source = $_GET;
                break;

            case 'post':
                // phpcs:ignore WordPress.Security.NonceVerification.Missing
                $source = $_POST;
                break;

            default:
                // phpcs:ignore WordPress.Security.NonceVerification.Recommended
                $source = $_REQUEST;
                break;
        }

        if (!isset($source[$key])) {
            return null;
        }

        $value = wp_unslash($source[$key]);

        if (is_array($value)) {
            return array_map(
                'sanitize_text_field',
                $value
            );
        }

        return sanitize_text_field($value);
    }
}