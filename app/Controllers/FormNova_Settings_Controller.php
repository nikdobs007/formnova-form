<?php

if (!defined('ABSPATH')) {
    exit;
}

class FormNova_Settings_Controller extends FormNova_BaseController
{
    public function settings_page()
    {
        if (
            isset($_POST['formnova_save_settings'])
        ) {

            check_admin_referer(
                'formnova_settings'
            );

            if (
                !current_user_can(
                    'manage_options'
                )
            ) {

                wp_die(
                    esc_html__('Permission denied.', 'formnova-form')
                );
            }

            update_option(
                'formnova_recaptcha_version',
                sanitize_text_field(
                    wp_unslash(
                        $_POST['recaptcha_version'] ?? 'none'
                    )
                )
            );

            update_option(

                'formnova_recaptcha_site_key',

                sanitize_text_field(

                    wp_unslash(
                        $_POST['recaptcha_site_key']
                        ?? ''
                    )

                )

            );

            update_option(

                'formnova_recaptcha_secret_key',

                sanitize_text_field(

                    wp_unslash(
                        $_POST['recaptcha_secret_key']
                        ?? ''
                    )

                )

            );

            add_settings_error(
                'formnova_settings',
                'saved',
                __('Settings saved.', 'formnova-form'),
                'updated'
            );
        }

        $recaptcha_version =
            get_option(
                'formnova_recaptcha_version',
                'none'
            );

        $site_key = get_option(
            'formnova_recaptcha_site_key',
            ''
        );

        $secret_key = get_option(
            'formnova_recaptcha_secret_key',
            ''
        );

        require FORMNOVA_PATH .
            'app/Views/admin/settings.php';
    }
}