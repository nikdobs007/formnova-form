<?php

if (!defined('ABSPATH')) {
    exit;
}

class FormNova_Database
{
    public static function forms_table()
    {
        global $wpdb;
        return esc_sql($wpdb->prefix . 'formnova_forms');
    }

    public static function fields_table()
    {
        global $wpdb;
        return esc_sql($wpdb->prefix . 'formnova_fields');
    }

    public static function field_meta_table()
    {
        global $wpdb;
        return esc_sql($wpdb->prefix . 'formnova_field_meta');
    }

    public static function submissions_table()
    {
        global $wpdb;
        return esc_sql($wpdb->prefix . 'formnova_submissions');
    }
    
    public static function get_var(
        $sql,
        $args = [],
        $cache_key = ''
    ) {
        global $wpdb;

        if (!empty($cache_key)) {
            $cached = wp_cache_get(
                $cache_key,
                'formnova'
            );

            if (false !== $cached) {
                return $cached;
            }
        }

        $prepared = $wpdb->prepare(
            $sql,
            ...$args
        );

        $result = $wpdb->get_var(
            $prepared
        );

        if (!empty($cache_key)) {
            wp_cache_set(
                $cache_key,
                $result,
                'formnova',
                HOUR_IN_SECONDS
            );
        }

        return $result;
    }

    public static function get_row(
        $sql,
        $args = [],
        $cache_key = ''
    ) {
        global $wpdb;

        if (!empty($cache_key)) {
            $cached = wp_cache_get(
                $cache_key,
                'formnova'
            );

            if (false !== $cached) {
                return $cached;
            }
        }

        $prepared = $wpdb->prepare(
            $sql,
            ...$args
        );

        $result = $wpdb->get_row(
            $prepared
        );

        if (!empty($cache_key)) {
            wp_cache_set(
                $cache_key,
                $result,
                'formnova',
                HOUR_IN_SECONDS
            );
        }

        return $result;
    }

    public static function get_results(
        $sql,
        $args = [],
        $cache_key = ''
    ) {
        global $wpdb;

        if (!empty($cache_key)) {
            $cached = wp_cache_get(
                $cache_key,
                'formnova'
            );

            if (false !== $cached) {
                return $cached;
            }
        }

        $prepared = $wpdb->prepare(
            $sql,
            ...$args
        );

        $result = $wpdb->get_results(
            $prepared
        );

        if (!empty($cache_key)) {
            wp_cache_set(
                $cache_key,
                $result,
                'formnova',
                HOUR_IN_SECONDS
            );
        }

        return $result;
    }

    public static function insert($table, $data, $format = [])
    {
        global $wpdb;

        return $wpdb->insert(
            $table,
            $data,
            $format
        );
    }

    public static function update(
        $table,
        $data,
        $where,
        $format = [],
        $where_format = []
    ) {
        global $wpdb;

        return $wpdb->update(
            $table,
            $data,
            $where,
            $format,
            $where_format
        );
    }

    public static function delete(
        $table,
        $where,
        $where_format = []
    ) {
        global $wpdb;

        return $wpdb->delete(
            $table,
            $where,
            $where_format
        );
    }

    public static function insert_id()
    {
        global $wpdb;

        return (int) $wpdb->insert_id;
    }
}