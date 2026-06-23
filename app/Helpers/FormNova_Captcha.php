<?php

if (!defined('ABSPATH')) {
    exit;
}

class FormNova_Captcha
{
    public static function verify()
    {
        if (
            !isset($_POST['formnova_nonce']) ||
            !wp_verify_nonce(
                sanitize_text_field(
                    wp_unslash($_POST['formnova_nonce'])
                ),
                'formnova_submit'
            )
        ) {
            wp_die(
                esc_html__(
                    'Security check failed.',
                    'formnova-form'
                )
            );
        }

        $response = sanitize_text_field(
            wp_unslash(
                $_POST['g-recaptcha-response'] ?? ''
            )
        );

        if (empty($response)) {
            return false;
        }

        $secret =
            get_option(
                'formnova_recaptcha_secret_key'
            );

        $version =
            get_option(
                'formnova_recaptcha_version',
                'none'
            );

        $result = wp_remote_post(
            'https://www.google.com/recaptcha/api/siteverify',
            [
                'timeout' => 15,

                'body' => [

                    'secret' =>
                        $secret,

                    'response' =>
                        $response
                ]
            ]
        );

        if (
            is_wp_error(
                $result
            )
        ) {

            return false;

        }

        $body = json_decode(
            wp_remote_retrieve_body(
                $result
            ),
            true
        );

        if (
            empty(
            $body['success']
        )
        ) {

            return false;

        }

        if (
            $version === 'v3'
            &&
            isset(
            $body['score']
        )
            &&
            $body['score'] < 0.5
        ) {

            return false;

        }

        return true;
    }
}