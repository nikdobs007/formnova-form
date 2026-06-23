<?php

if (!defined('ABSPATH')) {
    exit;
}

class FormNova_FieldMetaModel
{
    private $table;

    public function __construct()
    {
        $this->table =
            FormNova_Database::field_meta_table();
    }

    public function get($field_id)
    {
        $field_id = absint($field_id);

        if (!$field_id) {
            return false;
        }

        return FormNova_Database::get_row(
            "SELECT *
            FROM {$this->table}
            WHERE field_id = %d",
            [$field_id],
            'field_meta_' . $field_id
        );
    }

    public function save($field_id, $data)
    {
        global $wpdb;

        $field_id = absint($field_id);

        if (!$field_id) {
            return false;
        }

        /*
         * Check existing
         */
        $exists = FormNova_Database::get_var(
            "SELECT id
            FROM {$this->table}
            WHERE field_id = %d",
            [$field_id],
            'field_meta_exists_' . $field_id
        );

        $payload = [
            'field_id' => $field_id,
            'allowed_file_types' => sanitize_text_field(
                $data['allowed_file_types'] ?? ''
            ),
            'allowed_mimes' => sanitize_text_field(
                $data['allowed_mimes'] ?? ''
            ),
            'max_file_size' => absint(
                $data['max_file_size'] ?? 5
            ),
        ];

        if ($exists) {

            $result = FormNova_Database::update(
                $this->table,
                $payload,
                ['id' => absint($exists)],
                ['%d', '%s', '%s', '%d'],
                ['%d']
            );

        } else {

            $result = FormNova_Database::insert(
                $this->table,
                $payload,
                ['%d', '%s', '%s', '%d']
            );
        }

        /*
         * Clear cache after write
         */
        wp_cache_delete(
            'field_meta_' . $field_id,
            'formnova'
        );

        wp_cache_delete(
            'field_meta_exists_' . $field_id,
            'formnova'
        );

        return $result;
    }

    public function delete($field_id)
    {
        global $wpdb;

        $field_id = absint($field_id);

        if (!$field_id) {
            return false;
        }

        $meta_id = FormNova_Database::get_var(
            "SELECT id
            FROM {$this->table}
            WHERE field_id = %d",
            [$field_id],
            'field_meta_exists_' . $field_id
        );

        if (!$meta_id) {
            return false;
        }

        $result = FormNova_Database::delete(
            $this->table,
            ['id' => absint($meta_id)],
            ['%d']
        );

        /*
         * Clear cache
         */
        wp_cache_delete(
            'field_meta_' . $field_id,
            'formnova'
        );

        wp_cache_delete(
            'field_meta_exists_' . $field_id,
            'formnova'
        );

        return $result;
    }
}