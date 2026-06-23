<?php

if (!defined('ABSPATH')) {
    exit;
}

class FormNova_FieldController extends FormNova_BaseController
{
    private $field_model;

    public function __construct()
    {
        $this->field_model = new FormNova_Field_Model();
    }

    public function ajax_delete_field()
    {
        if (!defined('DOING_AJAX') || !DOING_AJAX) {
            wp_die('Direct access not allowed');
        }

        $request_method = isset($_SERVER['REQUEST_METHOD'])
            ? sanitize_text_field(wp_unslash($_SERVER['REQUEST_METHOD']))
            : '';

        if ('POST' !== $request_method) {
            wp_send_json_error([
                'message' => 'Invalid request'
            ]);
        }

        check_ajax_referer(
            'formnova_ajax_nonce',
            'nonce'
        );

        if (!current_user_can('manage_options')) {
            wp_send_json_error([
                'message' => 'Unauthorized'
            ]);
        }

        $field_id = absint($_POST['field_id'] ?? 0);
        $form_id = absint($_POST['form_id'] ?? 0);

        if (!$field_id || !$form_id) {
            wp_send_json_error([
                'message' => 'Invalid data'
            ]);
        }

        $field = $this->field_model->find($field_id);

        if (!$field || $field->form_id != $form_id) {
            wp_send_json_error([
                'message' => 'Field does not belong to this form'
            ]);
        }

        $result = $this->field_model->delete($field_id);

        if ($result) {
            wp_send_json_success([
                'message' => 'Field deleted'
            ]);
        }

        wp_send_json_error([
            'message' => 'Unable to delete field'
        ]);
    }

    public function ajax_get_field()
    {
        if (!defined('DOING_AJAX') || !DOING_AJAX) {
            wp_die('Direct access not allowed');
        }

        check_ajax_referer(
            'formnova_ajax_nonce',
            'nonce'
        );

        if (!current_user_can('manage_options')) {
            wp_send_json_error([
                'message' => 'Unauthorized'
            ]);
        }

        $field_id = isset($_POST['field_id'])
            ? absint(wp_unslash($_POST['field_id']))
            : 0;

        $field = $this->field_model->get(
            $field_id
        );

        if (!$field) {

            wp_send_json_error();

        }

        $field->allowed_file_types = '';
        $field->allowed_mimes = '';
        $field->max_file_size = 5;

        if ($field->type === 'file') {

            $meta_model = new FormNova_FieldMetaModel();

            $meta = $meta_model->get(
                $field_id
            );

            if ($meta) {

                $field->allowed_file_types =
                    $meta->allowed_file_types;

                $field->allowed_mimes =
                    $meta->allowed_mimes;

                $field->max_file_size =
                    absint(
                        $meta->max_file_size
                    );
            }
        }

        wp_send_json_success(
            $field
        );

    }
}