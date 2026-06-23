<?php

if (!defined('ABSPATH')) {
    exit;
}

class FormNova_Dashboard_Model
{
    public function total_forms()
    {
        $table = FormNova_Database::forms_table();

        return (int) FormNova_Database::get_var(
            "SELECT COUNT(*) FROM {$table}",
            [],
            'formnova_total_forms'
        );
    }

    public function total_submissions()
    {
        $table = FormNova_Database::submissions_table();

        return (int) FormNova_Database::get_var(
            "SELECT COUNT(*) FROM {$table}",
            [],
            'formnova_total_submissions'
        );
    }

    public function today_submissions()
    {
        $table = FormNova_Database::submissions_table();

        return (int) FormNova_Database::get_var(
            "SELECT COUNT(*)
         FROM {$table}
         WHERE DATE(submitted_at) = CURDATE()",
            [],
            'formnova_today_submissions'
        );
    }

    public function last_7_days()
    {
        $table = FormNova_Database::submissions_table();

        return (int) FormNova_Database::get_var(
            "SELECT COUNT(*)
         FROM {$table}
         WHERE submitted_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)",
            [],
            'formnova_last_7_days_submissions'
        );
    }

    public function top_form()
    {
        $table = FormNova_Database::submissions_table();

        return FormNova_Database::get_row(
            "SELECT form_id,
                COUNT(*) AS total
         FROM {$table}
         GROUP BY form_id
         ORDER BY total DESC
         LIMIT 1",
            [],
            'formnova_top_form'
        );
    }
}