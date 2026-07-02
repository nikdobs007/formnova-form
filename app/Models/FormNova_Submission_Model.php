<?php

if (!defined('ABSPATH')) {
    exit;
}

class FormNova_Submission_Model
{
    private $table;

    public function __construct()
    {
        $this->table =
            FormNova_Database::submissions_table();
    }

    public function create($form_id, $data)
    {
        $form_id = absint($form_id);

        if (!$form_id) {
            return false;
        }

        $result = FormNova_Database::insert(
            $this->table,
            [
                'form_id' => $form_id,
                'data' => wp_json_encode($data),
                'ip_address' => sanitize_text_field(
                    wp_unslash(
                        $_SERVER['REMOTE_ADDR'] ?? ''
                    )
                ),
                'submitted_at' => current_time('mysql')
            ],
            [
                '%d',
                '%s',
                '%s',
                '%s'
            ]
        );

        if ($result === false) {
            return new WP_Error(
                'formnova_submission_failed',
                __('Submission insert failed.', 'formnova-form')
            );
        }

        wp_cache_delete(
            'formnova_total_submissions',
            'formnova'
        );

        wp_cache_delete(
            'formnova_today_submissions',
            'formnova'
        );

        wp_cache_delete(
            'formnova_last_7_days_submissions',
            'formnova'
        );

        wp_cache_delete(
            'formnova_top_form',
            'formnova'
        );

        return FormNova_Database::insert_id();
    }

    public function get_all($form_id = null)
    {
        if (!empty($form_id)) {
            $form_id = absint($form_id);

            return FormNova_Database::get_results(
                "SELECT *
                FROM %i
                WHERE form_id = %d
                ORDER BY id DESC",
                [
                    $this->table,
                    $form_id
                ],
                'formnova_submissions_' . $form_id
            );
        }

        return FormNova_Database::get_results(
            "SELECT *
            FROM %i
            ORDER BY id DESC",
            [
                $this->table
            ],
            'formnova_all_submissions'
        );
    }

    public function delete($id)
    {
        $id = absint($id);

        if (!$id) {
            return false;
        }

        $result = FormNova_Database::delete(
            $this->table,
            ['id' => $id],
            ['%d']
        );

        if ($result === false) {
            return new WP_Error(
                'formnova_delete_failed',
                __('Failed to delete submission.', 'formnova-form')
            );
        }

        wp_cache_delete(
            'formnova_total_submissions',
            'formnova'
        );

        wp_cache_delete(
            'formnova_today_submissions',
            'formnova'
        );

        wp_cache_delete(
            'formnova_last_7_days_submissions',
            'formnova'
        );

        wp_cache_delete(
            'formnova_top_form',
            'formnova'
        );

        return $result;
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
            'formnova_submission_' . $id
        );
    }
}