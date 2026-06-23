<?php

if (!defined('ABSPATH')) {
    exit;
}

class FormNova_DashboardController extends FormNova_BaseController
{
    public function index()
    {
        $model = new FormNova_Dashboard_Model();

        $total_forms =
            $model->total_forms();

        $total_submissions =
            $model->total_submissions();

        $today_submissions =
            $model->today_submissions();

        $last_7_days =
            $model->last_7_days();

        $top_form =
            $model->top_form();

        require FORMNOVA_PATH .
            'app/Views/admin/dashboard.php';
    }
}