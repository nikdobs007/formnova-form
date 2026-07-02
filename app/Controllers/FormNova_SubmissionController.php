<?php

if (!defined('ABSPATH')) {
    exit;
}

class FormNova_SubmissionController extends FormNova_BaseController
{
    private $submission_model;

    public function __construct()
    {
        $this->submission_model = new FormNova_Submission_Model();
    }

    public function ajax_submit()
    {
        if (!defined('DOING_AJAX') || !DOING_AJAX) {
            wp_die('Direct access not allowed');
        }

        try {

            $this->handle_submission_ajax();

        } catch (\Throwable $e) {

            wp_send_json_error([
                'message' => $e->getMessage()
            ]);
        }

        wp_die();
    }

    private function handle_submission_ajax()
    {
        /*
         * Honeypot check
         */
        if (!empty($_POST['formnova_hp'])) {
            wp_send_json_error([
                'message' => __('Invalid request.', 'formnova-form')
            ]);
        }

        $start_time = intval($_POST['formnova_start_time'] ?? 0);

        if ($start_time && (time() - $start_time) < 3) {
            wp_send_json_error([
                'message' => 'Too fast submission detected.'
            ]);
        }

        /*
         * Nonce exists?
         */
        if (!isset($_POST['formnova_nonce'])) {
            wp_send_json_error([
                'message' => __('Security check failed.', 'formnova-form')
            ]);
        }

        /*
         * Verify nonce
         */
        if (
            !wp_verify_nonce(
                sanitize_text_field(
                    wp_unslash($_POST['formnova_nonce'])
                ),
                'formnova_submit'
            )
        ) {
            wp_send_json_error([
                'message' => __('Security check failed.', 'formnova-form')
            ]);
        }

        if (
            !isset($_SERVER['REQUEST_METHOD']) ||
            'POST' !== sanitize_text_field(wp_unslash($_SERVER['REQUEST_METHOD']))
        ) {
            wp_send_json_error([
                'message' => esc_html__('Invalid request method.', 'formnova-form')
            ]);
        }

        $form_id = absint($_POST['form_id'] ?? 0);

        $errors = [];

        /*
         * PHP upload errors
         */
        foreach ($_FILES as $field_name => $file) {

            if (!isset($file['error'])) {
                continue;
            }

            if ($file['error'] === UPLOAD_ERR_INI_SIZE) {

                $errors[$field_name] =
                    sprintf(
                        /* translators: %s: maximum upload file size. */
                        __('Server upload limit exceeded. Maximum allowed size is %s.', 'formnova-form'),
                        size_format(wp_max_upload_size())
                    );
            }

            if ($file['error'] === UPLOAD_ERR_FORM_SIZE) {

                $errors[$field_name] =
                    __('File exceeds allowed upload size.', 'formnova-form');
            }
        }

        if (!empty($errors)) {
            wp_send_json_error([
                'message' => wp_kses_post(
                    implode(
                        '<br>',
                        array_map('esc_html', $errors)
                    )
                )
            ]);
        }

        if (!$form_id) {
            wp_send_json_error([
                'message' => __('Invalid form.', 'formnova-form')
            ]);
        }

        $field_model = new FormNova_Field_Model();

        $fields = $field_model->get_by_form($form_id);

        if (empty($fields)) {
            wp_send_json_error([
                'message' => __('This form has no fields configured yet.', 'formnova-form')
            ]);
        }

        /*
         * Prevent completely empty submissions
         */
        $has_input = false;

        foreach ($fields as $field) {

            /*
             * File field
             */
            if (
                $field->type === 'file' &&
                isset($_FILES[$field->name]) &&
                !empty($_FILES[$field->name]['name'])
            ) {
                $has_input = true;
                break;
            }

            $value = isset($_POST[$field->name])
                ? $_POST[$field->name]
                : '';

            if (is_array($value)) {
                $value = array_map(
                    'sanitize_text_field',
                    wp_unslash($value)
                );
            } else {
                $value = sanitize_text_field(
                    wp_unslash($value)
                );
            }

            if (is_array($value)) {
                $value = array_filter($value);
            }

            if (!empty($value)) {
                $has_input = true;
                break;
            }
        }

        if (!$has_input) {
            wp_send_json_error([
                'message' => __('Please fill at least one field.', 'formnova-form')
            ]);
        }

        $form_model = new FormNova_Form_Model();

        $form = $form_model->get($form_id);

        $form_settings = [];

        if (!empty($form->settings)) {

            $form_settings = json_decode(
                $form->settings,
                true
            );

        }

        if (
            !empty($form_settings['captcha_enabled'])
        ) {

            if (!FormNova_Captcha::verify()) {

                wp_send_json_error(
                    [
                        'field' => 'captcha',

                        'message' =>
                            __(
                                'Captcha verification failed. Please refresh the page and try again.',
                                'formnova-form'
                            )
                    ]
                );
            }
        }

        /*
         * -----------------------------
         * VALIDATION
         * -----------------------------
         */
        foreach ($fields as $field) {

            $value = isset($_POST[$field->name])
                ? $_POST[$field->name]
                : '';

            if (is_array($value)) {
                $value = array_map(
                    'sanitize_text_field',
                    wp_unslash($value)
                );
            } else {
                $value = sanitize_text_field(
                    wp_unslash($value)
                );
            }

            /*
             * Text validation
             */
            if ($field->type === 'text' && !empty($value)) {
                if (!FormNova_Validator::text($value)) {
                    $errors[$field->name] = $field->label . ' is invalid.';
                }
            }

            /*
             * Telephone validation
             */
            if ($field->type === 'tel' && !empty($value)) {
                if (!FormNova_Validator::tel($value)) {
                    $errors[$field->name] = $field->label . ' must be a valid phone number.';
                }
            }

            /*
             * URL validation
             */
            if ($field->type === 'url' && !empty($value)) {
                if (!FormNova_Validator::url($value)) {
                    $errors[$field->name] = $field->label . ' must be a valid URL.';
                }
            }

            /*
             * Date validation
             */
            if ($field->type === 'date' && !empty($value)) {
                if (!FormNova_Validator::date($value)) {
                    $errors[$field->name] = $field->label . ' must be a valid date.';
                }
            }

            /*
             * Textarea validation
             */
            if ($field->type === 'textarea' && !empty($value)) {
                if (!FormNova_Validator::textarea($value)) {
                    $errors[$field->name] = $field->label . ' is invalid.';
                }
            }

            if ($field->type === 'file') {
                if (
                    $field->required &&
                    (
                        !isset($_FILES[$field->name]) ||
                        empty($_FILES[$field->name]['name'])
                    )
                ) {

                    $errors[$field->name] =
                        $field->label . ' is required.';
                }

                continue;
            }

            $value = isset($_POST[$field->name])
                ? $_POST[$field->name]
                : '';

            if (is_array($value)) {
                $value = array_map(
                    'sanitize_text_field',
                    wp_unslash($value)
                );
            } else {
                $value = sanitize_text_field(
                    wp_unslash($value)
                );
            }

            /*
             * Required validation
             */
            if ($field->required) {

                $is_empty = false;

                if (is_array($value)) {

                    $is_empty = empty(array_filter($value));

                } else {

                    $is_empty =
                        $value === null ||
                        trim((string) $value) === '';
                }

                if ($is_empty) {

                    $errors[$field->name] =
                        sprintf(
                            /* translators: %s: field label. */
                            __('%s is required.', 'formnova-form'),
                            $field->label
                        );

                    continue;
                }
            }

            /*
             * Email validation
             */
            if (
                $field->type === 'email' &&
                !empty($value)
            ) {
                if (!FormNova_Validator::email(wp_unslash($value))) {
                    $errors[$field->name] = 'Invalid email address.';
                }
            }

            /*
             * Number validation
             */
            if (
                $field->type === 'number' &&
                !empty($value)
            ) {
                if (!FormNova_Validator::number($value)) {
                    $errors[$field->name] =
                        $field->label . ' must be numeric.';
                }
            }

            /*
             * Checkbox validation
             */
            if (
                $field->type === 'checkbox' &&
                $field->required
            ) {
                if (!FormNova_Validator::checkbox_required($value)) {
                    $errors[$field->name] =
                        $field->label . ' is required.';
                }
            }

            /*
             * Select validation
             */
            if (
                $field->type === 'select' &&
                $field->required
            ) {
                if (!FormNova_Validator::select_required($value)) {
                    $errors[$field->name] =
                        $field->label . ' is required.';
                }
            }

            /*
             * Radio validation
             */
            if (
                $field->type === 'radio' &&
                $field->required
            ) {

                if (
                    $value === null ||
                    trim((string) $value) === ''
                ) {

                    $errors[$field->name] =
                        $field->label . ' is required.';
                }
            }
        }

        /*
         * -----------------------------
         * IF ERRORS
         * -----------------------------
         */
        if (!empty($errors)) {
            wp_send_json_error([
                'errors' => $errors
            ]);
        }

        /*
         * -----------------------------
         * CLEAN DATA
         * -----------------------------
         */
        $data = [];

        foreach ($fields as $field) {

            if ($field->type === 'file') {

                if (
                    isset($_FILES[$field->name]) &&
                    !empty($_FILES[$field->name]['name'])
                ) {

                    $meta_model = new FormNova_FieldMetaModel();

                    $meta = $meta_model->get(
                        $field->id
                    );

                    if (!$meta) {
                        $meta = new stdClass();
                        $meta->allowed_file_types = '';
                        $meta->allowed_mimes = '';
                        $meta->max_file_size = 5;
                    }

                    /*
                     * Allowed extensions
                     */
                    $allowed =
                        !empty($meta->allowed_file_types)
                        ? array_map(
                            'trim',
                            explode(
                                ',',
                                strtolower($meta->allowed_file_types)
                            )
                        )
                        : [];

                    /*
                     * File field configuration required
                     */
                    if (empty($allowed)) {

                        $errors[$field->name] =
                            __('Allowed file types not configured by administrator.', 'formnova-form');

                        continue;
                    }

                    /*
                     * Max size (MB)
                     */
                    $max_size =
                        !empty($meta->max_file_size)
                        ? intval($meta->max_file_size)
                        : 5;

                    /*
                     * Secure file detection
                     */
                    $file_name = sanitize_file_name($_FILES[$field->name]['name']);

                    $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

                    if (
                        isset($_FILES[$field->name]) &&
                        isset($_FILES[$field->name]['tmp_name'])
                    ) {
                        $tmp_name = isset($_FILES[$field->name]['tmp_name'])
                            ? sanitize_text_field($_FILES[$field->name]['tmp_name'])
                            : '';

                        $wp_filetype = wp_check_filetype_and_ext(
                            $tmp_name,
                            $file_name
                        );
                    }

                    $mime =
                        strtolower(
                            $wp_filetype['type'] ?? ''
                        );

                    /*
                     * Extension validation
                     */
                    if (
                        !empty($allowed) &&
                        !in_array(
                            $ext,
                            $allowed,
                            true
                        )
                    ) {

                        $errors[$field->name] =
                            sprintf(
                                /* translators: %s: comma-separated list of allowed file types. */
                                __('Allowed file types: %s', 'formnova-form'),
                                implode(', ', $allowed)
                            );

                        continue;
                    }

                    /*
                     * MIME validation
                     */
                    if (
                        !empty($meta->allowed_mimes)
                    ) {

                        $allowed_mimes =
                            array_map(
                                'trim',
                                explode(
                                    ',',
                                    strtolower(
                                        $meta->allowed_mimes
                                    )
                                )
                            );

                        if (
                            !in_array(
                                $mime,
                                $allowed_mimes,
                                true
                            )
                        ) {

                            $errors[$field->name] =
                                __('Invalid file MIME type.', 'formnova-form');

                            continue;
                        }
                    }

                    /*
                     * File size
                     */
                    $file_size = isset($_FILES[$field->name]['size'])
                        ? (int) $_FILES[$field->name]['size']
                        : 0;

                    /*
                     * STRICT SERVER SIDE VALIDATION (FINAL FIX)
                     */
                    if ($file_size <= 0) {
                        $errors[$field->name] = __('Invalid file upload.', 'formnova-form');
                        continue;
                    }

                    /*
                     * WordPress server limit check
                     */
                    $server_limit = wp_max_upload_size();

                    if ($file_size > $server_limit) {
                        $errors[$field->name] = sprintf(
                            /* translators: %s: server upload size limit. */
                            __('File exceeds server limit (%s).', 'formnova-form'),
                            size_format($server_limit)
                        );
                        continue;
                    }

                    /*
                     * Extra safety: prevent fake MIME large uploads
                     */
                    $tmp_name = isset($_FILES[$field->name]['tmp_name'])
                        ? sanitize_text_field($_FILES[$field->name]['tmp_name'])
                        : '';

                    if (empty($tmp_name) || !is_uploaded_file($tmp_name)) {
                        $errors[$field->name] = __(
                            'Possible file upload attack detected.',
                            'formnova-form'
                        );
                        continue;
                    }

                    /*
                     * Form limit
                     */
                    $max_bytes =
                        $max_size * 1024 * 1024;

                    if (
                        $file_size > $max_bytes
                    ) {

                        $errors[$field->name] =
                            sprintf(
                                /* translators: %d: maximum file size in megabytes. */
                                __('Maximum file size is %d MB', 'formnova-form'),
                                $max_size
                            );

                        continue;
                    }

                    $allowed_mimes = [];

                    $mime_types = wp_get_mime_types();

                    foreach ($allowed as $ext) {

                        foreach ($mime_types as $mime_ext => $mime) {

                            $ext_array = explode('|', $mime_ext);

                            if (
                                in_array(
                                    strtolower($ext),
                                    $ext_array,
                                    true
                                )
                            ) {
                                $allowed_mimes[$ext] = $mime;
                                break;
                            }
                        }
                    }

                    require_once ABSPATH . 'wp-admin/includes/file.php';
                    require_once ABSPATH . 'wp-admin/includes/image.php';
                    require_once ABSPATH . 'wp-admin/includes/media.php';

                    if (
                        isset($_FILES[$field->name]) &&
                        is_array($_FILES[$field->name])
                    ) {
                        $file = isset($_FILES[$field->name]) && is_array($_FILES[$field->name])
                            ? array_map('sanitize_text_field', $_FILES[$field->name])
                            : [];
                    }

                    $upload_overrides = [
                        'test_form' => false,
                        'mimes' => $allowed_mimes
                    ];

                    $movefile = wp_handle_upload(
                        $file,
                        $upload_overrides
                    );

                    if (!empty($movefile['error'])) {

                        $errors[$field->name] = sprintf(
                            /* translators: %s: upload error message. */
                            __('Upload failed: %s', 'formnova-form'),
                            $movefile['error']
                        );

                    } elseif (empty($movefile['url']) || empty($movefile['file'])) {

                        $errors[$field->name] = __(
                            'File upload failed.',
                            'formnova-form'
                        );

                    } else {

                        $filetype = wp_check_filetype(
                            $movefile['file'],
                            null
                        );

                        $attachment = [
                            'post_mime_type' => $filetype['type'],
                            'post_title' => sanitize_file_name($file['name']),
                            'post_content' => '',
                            'post_status' => 'inherit'
                        ];

                        $attach_id = wp_insert_attachment(
                            $attachment,
                            $movefile['file']
                        );

                        $attach_data = wp_generate_attachment_metadata(
                            $attach_id,
                            $movefile['file']
                        );

                        wp_update_attachment_metadata(
                            $attach_id,
                            $attach_data
                        );

                        $data[$field->name] = [
                            'url' => esc_url_raw($movefile['url']),
                            'id' => $attach_id
                        ];
                    }
                }

                continue;
            }

            $value = isset($_POST[$field->name])
                ? $_POST[$field->name]
                : '';

            if (is_array($value)) {
                $value = array_map(
                    'sanitize_text_field',
                    wp_unslash($value)
                );
            } else {
                $value = sanitize_text_field(
                    wp_unslash($value)
                );
            }

            if (is_array($value)) {
                $data[$field->name] = array_map(
                    'sanitize_text_field',
                    wp_unslash($value)
                );
            } else {
                $data[$field->name] = sanitize_text_field(
                    wp_unslash($value)
                );
            }
        }

        /*
         * -----------------------------
         * SAVE SUBMISSION
         * -----------------------------
         */
        $result = $this->submission_model->create(
            $form_id,
            $data
        );

        if ($result === false) {
            return new WP_Error(
                'formnova_db_error',
                __('Database operation failed.', 'formnova-form')
            );
        }

        /*
         * -----------------------------
         * EMAIL SYSTEM (NEW - Phase 6.4)
         * -----------------------------
         */

        $form_model = new FormNova_Form_Model();

        $form = $form_model->get($form_id);

        $settings = $form_model->get_settings($form_id);

        $form_title =
            !empty($form->title)
            ? $form->title
            : 'FormNova Form';

        /*
         * Build message body
         */
        $all_fields = '';
        $mail_attachments = [];

        foreach ($fields as $field) {

            $key = $field->name;

            if (!isset($data[$key])) {
                continue;
            }

            $value = $data[$key];

            if (
                $field->type === 'file' &&
                is_array($value)
            ) {
                $file_path =
                    get_attached_file(
                        $value['id']
                    );

                if (
                    $file_path &&
                    file_exists($file_path)
                ) {
                    $mail_attachments[] = $file_path;
                }

                $value = 'Attached';
            }

            if (is_array($value)) {
                $value = implode(', ', $value);
            }

            $all_fields .=
                $field->label . ': ' .
                $value . "<br>";
        }

        /*
         * -----------------------------
         * ADMIN EMAIL
         * -----------------------------
         */
        $admin_emails = [];

        if (!empty($settings['admin_email'])) {

            if (is_array($settings['admin_email'])) {
                $admin_emails = $settings['admin_email'];
            } else {
                $admin_emails = array_map(
                    'trim',
                    explode(',', $settings['admin_email'])
                );
            }
        }

        $subject_admin =
            $settings['subject_admin'] ??
            'New Form Submission - ' . $form_title;

        $message_admin =
            $settings['message_admin']
            ?? $all_fields;

        $message_admin =
            str_replace(
                '{form_title}',
                $form_title,
                $message_admin
            );

        foreach ($fields as $field) {

            $key = $field->name;

            if (!isset($data[$key])) {
                continue;
            }

            $value = $data[$key];

            if (
                $field->type === 'file' &&
                is_array($value)
            ) {
                $value = 'Attached';
            }

            if (is_array($value)) {
                $value = array_filter($value);
                $value = array_map('sanitize_text_field', $value);
                $value = implode(', ', $value);
            }

            $message_admin =
                str_replace(
                    '{' . $key . '}',
                    $value,
                    $message_admin
                );
        }

        $message_admin =
            str_replace(
                '{all_fields}',
                $all_fields,
                $message_admin
            );

        $headers = [
            'Content-Type: text/html; charset=UTF-8'
        ];

        if (!empty($settings['cc_email'])) {

            $cc_emails = is_array($settings['cc_email'])
                ? $settings['cc_email']
                : array_map(
                    'trim',
                    explode(',', $settings['cc_email'])
                );

            $headers[] =
                'Cc: ' . implode(', ', $cc_emails);
        }

        FormNova_Mailer::send(
            $admin_emails,
            $subject_admin,
            $message_admin,
            $headers,
            $mail_attachments
        );

        /*
         * -----------------------------
         * USER EMAIL (AUTO REPLY)
         * -----------------------------
         */
        if (!empty($settings['send_user_email'])) {

            $user_email = '';

            foreach ($data as $k => $v) {
                if (is_array($v)) {
                    continue;
                }

                if (is_email($v)) {
                    $user_email = $v;
                    break;
                }
            }

            if ($user_email) {

                $subject_user =
                    $settings['subject_user'] ??
                    'Thank you for your submission';

                $message_user =
                    $settings['message_user'] ??
                    'Thank you for contacting us.';

                $message_user = str_replace(
                    '{form_title}',
                    $form_title,
                    $message_user
                );

                foreach ($fields as $field) {

                    $key = $field->name;

                    if (!isset($data[$key])) {
                        continue;
                    }

                    $value = $data[$key];

                    if (
                        $field->type === 'file' &&
                        is_array($value)
                    ) {
                        $value = 'Attached';
                    }

                    if (is_array($value)) {
                        $value = array_filter($value);
                        $value = array_map('sanitize_text_field', $value);
                        $value = implode(', ', $value);
                    }

                    $message_user = str_replace(
                        '{' . $key . '}',
                        $value,
                        $message_user
                    );
                }

                $message_user = str_replace(
                    '{all_fields}',
                    $all_fields,
                    $message_user
                );

                FormNova_Mailer::send(
                    $user_email,
                    $subject_user,
                    $message_user
                );
            }
        }

        /*
         * -----------------------------
         * SUCCESS REDIRECT
         * -----------------------------
         */
        $remote_addr = sanitize_text_field(
            wp_unslash($_SERVER['REMOTE_ADDR'] ?? '')
        );

        $success_key =
            'formnova_success_' .
            $form_id . '_' .
            md5($remote_addr);

        set_transient(
            $success_key,
            1,
            60
        );

        wp_send_json_success([
            'message' => __('Form submitted successfully.', 'formnova-form')
        ]);
    }
}