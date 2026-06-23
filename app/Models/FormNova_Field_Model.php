<?php

if (!defined('ABSPATH')) {
    exit;
}

class FormNova_Field_Model
{
    private $table;

    public function __construct()
    {
        $this->table = FormNova_Database::fields_table();
    }

    public function get_by_form($form_id)
    {
        $form_id = absint($form_id);

        if (!$form_id) {
            return [];
        }

        $fields = FormNova_Database::get_results(
            "SELECT *
         FROM {$this->table}
         WHERE form_id = %d
         ORDER BY sort_order ASC, id ASC",
            [$form_id],
            'form_fields_' . $form_id
        );

        if (empty($fields)) {
            return [];
        }

        $meta_model = new FormNova_FieldMetaModel();

        foreach ($fields as &$field) {

            $field->allowed_file_types = '';
            $field->allowed_mimes = '';
            $field->max_file_size = 5;

            if ($field->type !== 'file') {
                continue;
            }

            $meta = $meta_model->get($field->id);

            if (!$meta) {
                continue;
            }

            $field->allowed_file_types =
                $meta->allowed_file_types ?? '';

            $field->allowed_mimes =
                $meta->allowed_mimes ?? '';

            $field->max_file_size =
                absint($meta->max_file_size ?? 5);
        }

        unset($field);

        return $fields;
    }

    public function create($data)
    {
        $options = '';

        if (!empty($data['options'])) {

            $raw_options = wp_unslash(
                $data['options']
            );

            $decoded = json_decode(
                $raw_options,
                true
            );

            if (
                json_last_error() === JSON_ERROR_NONE &&
                is_array($decoded)
            ) {

                $options = wp_json_encode(
                    array_values(
                        array_filter(
                            array_map(
                                function ($opt) {
                                    return sanitize_text_field(
                                        trim($opt)
                                    );
                                },
                                $decoded
                            )
                        )
                    )
                );

            } else {

                $options = wp_json_encode(
                    array_values(
                        array_filter(
                            array_map(
                                function ($opt) {
                                    return sanitize_text_field(
                                        trim($opt)
                                    );
                                },
                                preg_split(
                                    '/\r\n|\r|\n/',
                                    $raw_options
                                )
                            )
                        )
                    )
                );
            }
        }

        $form_id = absint(
            $data['form_id']
        );

        $result = FormNova_Database::insert(
            $this->table,
            [
                'form_id' => $form_id,
                'type' => FormNova_Sanitizer::text(
                    $data['type']
                ),
                'label' => FormNova_Sanitizer::text(
                    $data['label']
                ),
                'name' => FormNova_Sanitizer::key(
                    $data['name']
                ),
                'required' => !empty(
                    $data['required']
                ) ? 1 : 0,
                'placeholder' => FormNova_Sanitizer::text(
                    $data['placeholder'] ?? ''
                ),
                'custom_class' => FormNova_Sanitizer::text(
                    $data['custom_class'] ?? ''
                ),
                'options' => $options,
                'sort_order' => isset(
                    $data['sort_order']
                )
                    ? absint(
                        $data['sort_order']
                    )
                    : 0,
            ],
            [
                '%d',
                '%s',
                '%s',
                '%s',
                '%d',
                '%s',
                '%s',
                '%s',
                '%d'
            ]
        );

        if ($result === false) {
            return false;
        }

        /*
         * Clear form fields cache
         */
        wp_cache_delete(
            'form_fields_' . $form_id,
            'formnova'
        );

        return FormNova_Database::insert_id();
    }

    public function get($id)
    {
        $id = absint($id);

        if (!$id) {
            return false;
        }

        $field = FormNova_Database::get_row(
            "SELECT *
        FROM {$this->table}
        WHERE id = %d",
            [$id],
            'field_' . $id
        );

        if (!$field) {
            return false;
        }

        if ($field->type === 'file') {

            $meta_model = new FormNova_FieldMetaModel();

            $meta = $meta_model->get($field->id);

            $field->allowed_file_types = '';
            $field->allowed_mimes = '';
            $field->max_file_size = 5;

            if ($meta) {

                $field->allowed_file_types =
                    $meta->allowed_file_types ?? '';

                $field->allowed_mimes =
                    $meta->allowed_mimes ?? '';

                $field->max_file_size =
                    absint($meta->max_file_size ?? 5);
            }
        }

        return $field;
    }

    public function update($id, $data)
    {
        $options = '';

        if (!empty($data['options'])) {

            $raw_options = wp_unslash($data['options']);

            $decoded = json_decode($raw_options, true);

            if (
                json_last_error() === JSON_ERROR_NONE &&
                is_array($decoded)
            ) {

                $options = wp_json_encode(
                    array_values(
                        array_filter(
                            array_map(
                                function ($opt) {
                                    return sanitize_text_field(
                                        trim($opt)
                                    );
                                },
                                $decoded
                            )
                        )
                    )
                );

            } else {

                $options = wp_json_encode(
                    array_values(
                        array_filter(
                            array_map(
                                'trim',
                                preg_split(
                                    '/\r\n|\r|\n/',
                                    $raw_options
                                )
                            )
                        )
                    )
                );
            }
        }

        $id = absint($id);

        if (!$id) {
            return false;
        }

        /*
         * Existing field before update
         */
        $existing_field = $this->get($id);

        $result = FormNova_Database::update(
            $this->table,
            [
                'type' => sanitize_text_field(
                    $data['type']
                ),
                'label' => sanitize_text_field(
                    $data['label']
                ),
                'name' => sanitize_key(
                    $data['name']
                ),
                'required' => !empty($data['required'])
                    ? 1
                    : 0,
                'placeholder' => FormNova_Sanitizer::text(
                    $data['placeholder'] ?? ''
                ),
                'custom_class' => FormNova_Sanitizer::text(
                    $data['custom_class'] ?? ''
                ),
                'options' => $options,
                'sort_order' => isset($data['sort_order'])
                    ? absint($data['sort_order'])
                    : 0,
            ],
            [
                'id' => $id
            ],
            [
                '%s',
                '%s',
                '%s',
                '%d',
                '%s',
                '%s',
                '%s',
                '%d'
            ],
            [
                '%d'
            ]
        );

        /*
         * Clear caches
         */
        wp_cache_delete(
            'field_' . $id,
            'formnova'
        );

        if (
            $existing_field &&
            !empty($existing_field->form_id)
        ) {

            wp_cache_delete(
                'form_fields_' . $existing_field->form_id,
                'formnova'
            );
        }

        return $result;
    }

    public function delete($field_id)
    {
        $field_id = absint($field_id);

        if (!$field_id) {
            return false;
        }

        $field = $this->get($field_id);

        if (!$field) {
            return false;
        }

        /*
         * Delete file meta if file field
         */
        if ($field->type === 'file') {

            $meta_model = new FormNova_FieldMetaModel();

            $meta_model->delete(
                $field_id
            );
        }

        $result = FormNova_Database::delete(
            $this->table,
            [
                'id' => $field_id
            ],
            [
                '%d'
            ]
        );

        /*
         * Clear caches
         */
        wp_cache_delete(
            'field_' . $field_id,
            'formnova'
        );

        wp_cache_delete(
            'form_fields_' . $field->form_id,
            'formnova'
        );

        wp_cache_delete(
            'field_name_' . md5(
                $field->form_id . '_' . $field->name
            ),
            'formnova'
        );

        return $result;
    }

    public function delete_by_form($form_id)
    {
        $form_id = absint($form_id);

        if (!$form_id) {
            return false;
        }

        $field_ids = FormNova_Database::get_results(
            "SELECT id
        FROM {$this->table}
        WHERE form_id=%d",
            [$form_id],
            'form_field_ids_' . $form_id
        );

        if (!empty($field_ids)) {

            $meta_table =
                FormNova_Database::field_meta_table();

            foreach ($field_ids as $row) {

                FormNova_Database::delete(
                    $meta_table,
                    [
                        'field_id' => absint($row->id)
                    ],
                    [
                        '%d'
                    ]
                );

                wp_cache_delete(
                    'field_meta_' . absint($row->id),
                    'formnova'
                );

                wp_cache_delete(
                    'field_meta_exists_' . absint($row->id),
                    'formnova'
                );

                wp_cache_delete(
                    'field_' . absint($row->id),
                    'formnova'
                );
            }
        }

        $result = FormNova_Database::delete(
            $this->table,
            [
                'form_id' => $form_id
            ],
            [
                '%d'
            ]
        );

        wp_cache_delete(
            'form_fields_' . $form_id,
            'formnova'
        );

        wp_cache_delete(
            'form_field_ids_' . $form_id,
            'formnova'
        );

        return $result;
    }

    public function update_order(
        $field_id,
        $position
    ) {
        $field_id = absint($field_id);
        $position = absint($position);

        if (!$field_id) {
            return false;
        }

        $field = $this->get($field_id);

        $result = FormNova_Database::update(
            $this->table,
            [
                'sort_order' => $position
            ],
            [
                'id' => $field_id
            ],
            [
                '%d'
            ],
            [
                '%d'
            ]
        );

        /*
         * Clear cache
         */
        wp_cache_delete(
            'field_' . $field_id,
            'formnova'
        );

        if ($field && !empty($field->form_id)) {

            wp_cache_delete(
                'form_fields_' . $field->form_id,
                'formnova'
            );
        }

        return $result;
    }

    public function field_name_exists(
        $form_id,
        $name
    ) {

        $form_id = absint($form_id);
        $name = sanitize_key($name);

        if (!$form_id || empty($name)) {
            return false;
        }

        return FormNova_Database::get_var(
            "SELECT id
                FROM {$this->table}
                WHERE form_id=%d
                AND name=%s",
            [
                $form_id,
                $name
            ],
            'field_name_exists_' . md5(
                $form_id . '_' . $name
            )
        );
    }
}