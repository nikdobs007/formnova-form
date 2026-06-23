<?php

if (!defined('ABSPATH')) {
    exit;
}

class FormNova_FrontendController extends FormNova_BaseController
{
    private $form_model;

    private $field_model;

    public function __construct()
    {
        $this->form_model = new FormNova_Form_Model();
        $this->field_model = new FormNova_Field_Model();
    }

    public function render_shortcode($atts)
    {
        $atts = shortcode_atts(
            [
                'id' => 0
            ],
            $atts
        );

        $form_id = absint(
            $atts['id']
        );

        if (!$form_id) {
            return '';
        }

        $formnova_form = $this->form_model->get(
            $form_id
        );

        if (!$formnova_form) {
            return '<p>Form not found.</p>';
        }

        $formnova_fields = $this->field_model
            ->get_by_form(
                $form_id
            );

        ob_start();

        require FORMNOVA_PATH .
            'app/Views/frontend/form.php';

        return ob_get_clean();
    }

    public function enqueue_assets()
    {
        if (is_admin()) {
            return;
        }

        wp_enqueue_style(
            'formnova-frontend',
            FORMNOVA_URL . 'assets/css/frontend.css',
            [],
            FORMNOVA_VERSION
        );

        wp_enqueue_script(
            'formnova-frontend-js',
            FORMNOVA_URL . 'assets/js/frontend.js',
            [],
            FORMNOVA_VERSION,
            true
        );

        $version =
            get_option(
                'formnova_recaptcha_version',
                'none'
            );

        $site_key =
            get_option(
                'formnova_recaptcha_site_key',
                ''
            );

        wp_localize_script(
            'formnova-frontend-js',
            'formnova_ajax',
            [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('formnova_submit'),
                'recaptcha_version' => $version,
                'site_key' => $site_key
            ]
        );

        if (
            $version === 'v2'
        ) {

            wp_enqueue_script(
                'google-recaptcha',
                'https://www.google.com/recaptcha/api.js',
                [],
                FORMNOVA_VERSION,
                true
            );

        } elseif (
            $version === 'v3'
            &&
            !empty($site_key)
        ) {

            wp_enqueue_script(
                'google-recaptcha',
                'https://www.google.com/recaptcha/api.js?render=' .
                rawurlencode(
                    $site_key
                ),
                [],
                FORMNOVA_VERSION,
                true
            );

        }
    }
}