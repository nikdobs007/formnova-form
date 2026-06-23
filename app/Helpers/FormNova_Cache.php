<?php

if (!defined('ABSPATH')) {
    exit;
}

class FormNova_Cache
{
    const GROUP = 'formnova';

    public static function get($key)
    {
        return wp_cache_get(
            $key,
            self::GROUP
        );
    }

    public static function set(
        $key,
        $value,
        $expire = HOUR_IN_SECONDS
    ) {
        wp_cache_set(
            $key,
            $value,
            self::GROUP,
            $expire
        );
    }

    public static function delete($key)
    {
        wp_cache_delete(
            $key,
            self::GROUP
        );
    }
}