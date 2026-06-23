<?php

if (!defined('ABSPATH')) {
    exit;
}

class FormNova_Mailer
{
    public static function send(
        $to,
        $subject,
        $message,
        $headers = [],
        $attachments = []
    ) {
        $recipients = is_array($to)
            ? $to
            : explode(',', $to);

        $recipients = array_filter(
            array_map(
                function ($email) {
                    $email = sanitize_email(trim($email));
                    return is_email($email)
                        ? $email
                        : false;
                },
                $recipients
            )
        );

        if (empty($recipients)) {
            return false;
        }

        $subject = sanitize_text_field($subject);

        // Header injection protection
        $subject = str_replace(
            ["\r", "\n"],
            '',
            $subject
        );

        $default_headers = [
            'Content-Type: text/html; charset=UTF-8'
        ];

        $headers = array_merge(
            $default_headers,
            $headers
        );

        return wp_mail(
            $recipients,
            $subject,
            wp_kses_post(
                nl2br($message)
            ),
            $headers,
            $attachments
        );
    }
}