<?php

if (!defined('ABSPATH')) {
    exit;
}

class FormNova_Activator
{
    public static function activate()
    {
        self::create_tables();

        flush_rewrite_rules();
    }

    private static function create_tables()
    {
        global $wpdb;

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $charset_collate = $wpdb->get_charset_collate();

        $forms_table = FormNova_Database::forms_table();
        $fields_table = FormNova_Database::fields_table();
        $field_meta_table = FormNova_Database::field_meta_table();
        $submissions_table = FormNova_Database::submissions_table();

        $sql = "

        CREATE TABLE {$forms_table} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            title VARCHAR(255) NOT NULL,
            user_id BIGINT(20) NOT NULL DEFAULT 0,
            settings LONGTEXT NULL,
            custom_class varchar(255) NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY (id)
        ) {$charset_collate};

        CREATE TABLE {$fields_table} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            form_id BIGINT UNSIGNED NOT NULL,
            type VARCHAR(50) NOT NULL,
            label VARCHAR(255) NOT NULL,
            name VARCHAR(255) NOT NULL,
            required TINYINT(1) DEFAULT 0,
            placeholder VARCHAR(255) NULL,
            options LONGTEXT NULL,
            sort_order INT DEFAULT 0,
            custom_class varchar(255) NULL,
            PRIMARY KEY (id),
            KEY form_id (form_id)
        ) {$charset_collate};

        CREATE TABLE {$field_meta_table} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            field_id BIGINT UNSIGNED NOT NULL,
            allowed_file_types TEXT NULL,
            allowed_mimes TEXT NULL,
            max_file_size INT DEFAULT 0,
            PRIMARY KEY (id),
            KEY field_id (field_id)
        ) {$charset_collate};

        CREATE TABLE {$submissions_table} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            form_id BIGINT UNSIGNED NOT NULL,
            data LONGTEXT NOT NULL,
            ip_address VARCHAR(100) NULL,
            submitted_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY form_id (form_id)
        ) {$charset_collate};
        ";

        dbDelta($sql);
    }
}