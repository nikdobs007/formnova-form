<?php

if (!defined('ABSPATH')) {
    exit;
}

class FormNova_Form_Model
{
    private $table;

    public function __construct()
    {
        $this->table = FormNova_Database::forms_table();
    }

    public function get_all()
    {
        return FormNova_Database::get_results(
            "SELECT *
            FROM %i
            ORDER BY id DESC",
            [
                $this->table
            ],
            'all_forms'
        );
    }

    public function get($id)
    {
        $id = absint($id);

        if (!$id) {
            return false;
        }

        return FormNova_Database::get_row(
            "SELECT *
            FROM %i
            WHERE id = %d",
            [
                $this->table,
                $id
            ],
            'form_' . $id
        );
    }

    public function create($title, $settings = '', $custom_class = '')
    {
        $now = current_time('mysql');

        $result = FormNova_Database::insert(
            $this->table,
            [
                'title' => sanitize_text_field($title),
                'settings' => $settings,
                'user_id' => get_current_user_id(),
                'custom_class' => FormNova_Sanitizer::text(
                    $custom_class
                ),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                '%s',
                '%s',
                '%d',
                '%s',
                '%s',
                '%s'
            ]
        );

        if ($result === false) {
            return new WP_Error(
                'formnova_create_failed',
                sprintf(
                    /* translators: %s: database error message. */
                    __('Form creation failed: %s', 'formnova-form')
                )
            );
        }

        wp_cache_delete(
            'all_forms',
            'formnova'
        );

        return FormNova_Database::insert_id();
    }

    public function update($id, $title, $settings = '', $custom_class = '')
    {
        $id = absint($id);

        if (!$id) {
            return false;
        }

        $result = FormNova_Database::update(
            $this->table,
            [
                'title' => FormNova_Sanitizer::text($title),
                'settings' => $settings,
                'custom_class' => FormNova_Sanitizer::text(
                    $custom_class
                ),
                'updated_at' => current_time('mysql')
            ],
            [
                'id' => $id
            ],
            [
                '%s',
                '%s',
                '%s',
                '%s'
            ],
            [
                '%d'
            ]
        );

        if ($result === false) {
            return new WP_Error(
                'formnova_db_error',
            );
        }

        wp_cache_delete(
            'form_' . $id,
            'formnova'
        );

        wp_cache_delete(
            'all_forms',
            'formnova'
        );

        return $result;
    }

    public function delete($id)
    {
        $id = absint($id);

        if (!$id) {
            return false;
        }

        $field_model = new FormNova_Field_Model();

        $field_model->delete_by_form($id);

        $result = FormNova_Database::delete(
            $this->table,
            [
                'id' => $id
            ],
            [
                '%d'
            ]
        );

        wp_cache_delete(
            'form_' . $id,
            'formnova'
        );

        wp_cache_delete(
            'all_forms',
            'formnova'
        );

        return $result;
    }

    public function get_settings($form_id)
    {
        $form_id = absint($form_id);

        if (!$form_id) {
            return [];
        }

        $row = FormNova_Database::get_var(
            "SELECT settings
            FROM %i
            WHERE id = %d",
            [
                $this->table,
                $form_id
            ],
            'form_settings_' . $form_id
        );

        $settings = json_decode(
            $row,
            true
        );

        return is_array($settings)
            ? $settings
            : [];
    }

    public function update_settings(
        $form_id,
        $settings
    ) {

        $form_id = absint($form_id);

        if (!$form_id) {
            return false;
        }

        $result = FormNova_Database::update(
            $this->table,
            [
                'settings' => wp_json_encode($settings)
            ],
            [
                'id' => $form_id
            ],
            [
                '%s'
            ],
            [
                '%d'
            ]
        );

        wp_cache_delete(
            'form_settings_' . $form_id,
            'formnova'
        );

        wp_cache_delete(
            'form_' . $form_id,
            'formnova'
        );

        return $result;
    }

}