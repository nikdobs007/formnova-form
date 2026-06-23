<?php

if (!defined('ABSPATH')) {
    exit;
}

class FormNova_SubmissionAdminController extends FormNova_BaseController
{
    private $submission_model;

    public function __construct()
    {
        $this->submission_model = new FormNova_Submission_Model();
    }


    public function get_all($form_id = null)
    {
        return $this->submission_model->get_all($form_id);
    }
}