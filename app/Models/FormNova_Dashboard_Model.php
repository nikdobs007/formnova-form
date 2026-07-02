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
            "SELECT COUNT(*) FROM %i",
            [$table],
            'formnova_total_forms'
        );
    }

    public function total_submissions()
    {
        $table = FormNova_Database::submissions_table();

        return (int) FormNova_Database::get_var(
            "SELECT COUNT(*) FROM %i",
            [$table],
            'formnova_total_submissions'
        );
    }

    public function today_submissions()
    {
        $table = FormNova_Database::submissions_table();

        return (int) FormNova_Database::get_var(
            "SELECT COUNT(*) FROM %i WHERE DATE(submitted_at) = CURDATE()",
            [$table],
            'formnova_today_submissions'
        );
    }

    public function last_7_days()
    {
        $table = FormNova_Database::submissions_table();

        return (int) FormNova_Database::get_var(
            "SELECT COUNT(*) 
             FROM %i
             WHERE submitted_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)",
            [$table],
            'formnova_last_7_days_submissions'
        );
    }

    public function top_form()
    {
        $table = FormNova_Database::submissions_table();

        return FormNova_Database::get_row(
            "SELECT form_id, COUNT(*) AS total
             FROM %i
             GROUP BY form_id
             ORDER BY total DESC
             LIMIT 1",
            [$table],
            'formnova_top_form'
        );
    }
}