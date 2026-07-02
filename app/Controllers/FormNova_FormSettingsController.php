<?php

if (!defined('ABSPATH')) {
    exit;
}

class FormNova_FormSettingsController extends FormNova_BaseController
{
    public function __construct()
    {

    }
    public function save()
    {
        if (
            !isset($_POST['formnova_settings_nonce'])
        ) {
            return;
        }

        if (
            !wp_verify_nonce(
                sanitize_text_field(
                    wp_unslash($_POST['formnova_settings_nonce'])
                ),
                'formnova_save_settings'
            )
        ) {
            return;
        }

        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        if (!isset($_POST['form_id'])) {
            wp_die(
                esc_html__('Form ID is missing.', 'formnova-form')
            );
        }

        $form_id = absint(
            wp_unslash($_POST['form_id'])
        );

        if (!$form_id) {
            return;
        }

        $raw_admin_email = isset($_POST['admin_email'])
            ? sanitize_text_field(wp_unslash($_POST['admin_email']))
            : '';

        $admin_emails = array_filter(
            array_map(
                'sanitize_email',
                array_map(
                    'trim',
                    explode(',', $raw_admin_email)
                )
            )
        );

        $raw_cc_email = isset($_POST['cc_email'])
            ? sanitize_text_field(wp_unslash($_POST['cc_email']))
            : '';

        $cc_emails = array_filter(
            array_map(
                'sanitize_email',
                array_map(
                    'trim',
                    explode(',', $raw_cc_email)
                )
            )
        );

        $settings = [
            'admin_email' => $admin_emails,
            'cc_email' => $cc_emails,
            'subject_admin' => sanitize_text_field(
                wp_unslash($_POST['subject_admin'] ?? '')
            ),

            'subject_user' => sanitize_text_field(
                wp_unslash($_POST['subject_user'] ?? '')
            ),

            'message_admin' => sanitize_textarea_field(
                wp_unslash($_POST['message_admin'] ?? '')
            ),

            'message_user' => sanitize_textarea_field(
                wp_unslash($_POST['message_user'] ?? '')
            ),
            'send_user_email' => !empty($_POST['send_user_email']) ? 1 : 0,
            'captcha_enabled' => !empty($_POST['captcha_enabled']) ? 1 : 0,
        ];

        $model = new FormNova_Form_Model();

        $model->update_settings($form_id, $settings);

        wp_safe_redirect(
            admin_url(
                'admin.php?page=formnova-edit&id=' . $form_id . '&settings=1'
            )
        );

        exit;
    }
}