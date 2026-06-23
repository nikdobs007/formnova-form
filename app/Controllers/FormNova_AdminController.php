<?php

if (!defined('ABSPATH')) {
    exit;
}

class FormNova_AdminController extends FormNova_BaseController
{
    /**
     * @var FormNova_Form_Model
     */
    private $form_model;

    private $field_model;

    public function __construct()
    {
        $this->form_model = new FormNova_Form_Model();
        $this->field_model = new FormNova_Field_Model();
    }

    public function handle_requests()
    {

    }
    /**
     * Register admin menu
     */
    public function register_menu()
    {
        add_menu_page(
            __('FormNova', 'formnova-form'),
            __('FormNova', 'formnova-form'),
            'manage_options',
            'formnova',
            [$this, 'forms_page'],
            'dashicons-feedback',
            26
        );

        add_submenu_page(
            'formnova',
            __('Forms', 'formnova-form'),
            __('Forms', 'formnova-form'),
            'manage_options',
            'formnova',
            [$this, 'forms_page']
        );

        add_submenu_page(
            'formnova',
            __('Add New', 'formnova-form'),
            __('Add New', 'formnova-form'),
            'manage_options',
            'formnova-add',
            [$this, 'add_form_page']
        );

        add_submenu_page(
            'formnova',
            __('Submissions', 'formnova-form'),
            __('Submissions', 'formnova-form'),
            'manage_options',
            'formnova-submissions',
            [$this, 'submissions_page']
        );

        add_submenu_page(
            null,
            __('View Submission', 'formnova-form'),
            __('View Submission', 'formnova-form'),
            'manage_options',
            'formnova-submission-view',
            [$this, 'submission_view_page']
        );

        add_submenu_page(
            'formnova',
            __('Analytics', 'formnova-form'),
            __('Analytics', 'formnova-form'),
            'manage_options',
            'formnova-dashboard',
            [$this, 'dashboard_page']
        );

        add_submenu_page(
            'formnova',
            __('Settings', 'formnova-form'),
            __('Settings', 'formnova-form'),
            'manage_options',
            'formnova-settings',
            [$this, 'settings_page']
        );

        add_submenu_page(
            null,
            'Edit Form',
            'Edit Form',
            'manage_options',
            'formnova-edit',
            [$this, 'edit_form_page']
        );

        add_submenu_page(
            null,
            'Delete Form',
            'Delete Form',
            'manage_options',
            'formnova-delete',
            [$this, 'delete_form_page']
        );
    }

    /**
     * Forms list page
     */
    public function forms_page()
    {
        $table = new FormNova_Forms_Table();

        $table->prepare_items();

        $this->view(
            'admin/forms-list',
            [
                'table' => $table
            ]
        );
    }

    /**
     * Add form page
     */
    public function add_form_page()
    {
        $formnova_form = null;

        $formnova_fields = [

            (object) [
                'id' => 0,
                'label' => 'First Name',
                'name' => 'first_name',
                'type' => 'text',
                'placeholder' => 'First Name',
                'required' => 1
            ],

            (object) [
                'id' => 0,
                'label' => 'Last Name',
                'name' => 'last_name',
                'type' => 'text',
                'placeholder' => 'Last Name',
                'required' => 1
            ],

            (object) [
                'id' => 0,
                'label' => 'Email',
                'name' => 'email',
                'type' => 'email',
                'placeholder' => 'Email',
                'required' => 1
            ],

            (object) [
                'id' => 0,
                'label' => 'Address',
                'name' => 'address',
                'type' => 'textarea',
                'required' => 0
            ]

        ];

        if (!empty($_GET['id'])) {

            $id = absint(wp_unslash($_GET['id']));

            if (
                !isset($_GET['_wpnonce']) ||
                !wp_verify_nonce(
                    sanitize_text_field(
                        wp_unslash($_GET['_wpnonce'])
                    ),
                    'formnova_edit_nonce'
                )
            ) {
                wp_die(
                    esc_html__(
                        'Security check failed.',
                        'formnova-form'
                    )
                );
            }

            $formnova_form = $this->form_model->get($id);

            if ($formnova_form) {

                $field_model = new FormNova_Field_Model();

                $formnova_fields = $field_model->get_by_form(
                    $id
                );
            }
        }

        $this->view(
            'admin/form-builder',
            [
                'formnova_form' => $formnova_form,
                'formnova_fields' => $formnova_fields,
            ]
        );
    }

    /**
     * Save form
     */
    public function ajax_save_form()
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

        $title = sanitize_text_field(
            wp_unslash($_POST['title'] ?? '')
        );

        if (empty($title)) {

            wp_send_json_error([
                'message' => 'Form title required'
            ]);

        }

        $form_class =
            sanitize_text_field(
                wp_unslash($_POST['custom_class'] ?? '')
            );

        $admin_email_input = isset($_POST['admin_email'])
            ? sanitize_text_field(wp_unslash($_POST['admin_email']))
            : '';

        $admin_emails = array_filter(
            array_map(
                'sanitize_email',
                array_map(
                    'trim',
                    explode(',', $admin_email_input)
                )
            )
        );

        $raw_cc_email = isset($_POST['cc_email'])
            ? sanitize_text_field(wp_unslash($_POST['cc_email']))
            : '';

        $cc_email = array_filter(
            array_map(
                'sanitize_email',
                array_map(
                    'trim',
                    explode(',', $raw_cc_email)
                )
            )
        );

        $settings = [

            'admin_email' => implode(',', $admin_emails),

            'cc_email' => implode(',', $cc_email),

            'subject_admin' => sanitize_text_field(
                wp_unslash($_POST['subject_admin'] ?? '')
            ),

            'subject_user' => sanitize_text_field(
                wp_unslash($_POST['subject_user'] ?? '')
            ),

            'message_admin' => wp_kses_post(
                wp_unslash($_POST['message_admin'] ?? '')
            ),

            'message_user' => wp_kses_post(
                wp_unslash($_POST['message_user'] ?? '')
            ),

            'send_user_email' =>
                !empty($_POST['send_user_email']) ? 1 : 0,

            'captcha_enabled' =>
                !empty($_POST['captcha_enabled'])
                ? 1
                : 0
        ];

        $settings_json = wp_json_encode(
            $settings
        );

        $raw_fields = isset($_POST['fields'])
            ? sanitize_textarea_field(
                wp_unslash($_POST['fields'])
            )
            : '[]';

        $fields = json_decode(
            $raw_fields,
            true
        );

        $deleted_fields = json_decode(
            sanitize_text_field(
                wp_unslash(
                    $_POST['deleted_fields'] ?? '[]'
                )
            ),
            true
        );

        if (
            !is_array(
                $deleted_fields
            )
        ) {

            $deleted_fields = [];
        }

        if (json_last_error() !== JSON_ERROR_NONE) {
            $fields = [];
        }

        if (!is_array($fields)) {
            $fields = [];
        }

        $form_id = absint(
            $_POST['form_id'] ?? 0
        );

        try {

            if ($form_id > 0) {

                $result = $this->form_model->update(
                    $form_id,
                    $title,
                    $settings_json,
                    $form_class
                );

                if ($result === false) {

                    wp_send_json_error([
                        'message' => 'Unable to update form'
                    ]);
                }

            } else {

                $form_id = $this->form_model->create(
                    $title,
                    $settings_json,
                    $form_class
                );

                if (!$form_id) {

                    wp_send_json_error([
                        'message' => 'Unable to create form'
                    ]);
                }
            }

            $used_names = [];

            $existing_ids = [];

            $allowed_types = [

                'text',
                'email',
                'number',
                'tel',
                'textarea',
                'select',
                'radio',
                'checkbox',
                'date',
                'url',
                'file'
            ];

            $field_meta_model = new FormNova_FieldMetaModel();

            /*
             * Delete removed fields
             */
            foreach ($deleted_fields as $field_id) {

                $field_id = absint($field_id);

                if (!$field_id) {
                    continue;
                }

                $field = $this->field_model->get($field_id);

                if (!$field) {
                    continue;
                }

                $this->field_model->delete(
                    $field_id
                );
            }

            foreach ($fields as $index => $field) {

                /*
                 * Required validation
                 */
                if (
                    empty($field['label']) ||
                    empty($field['name']) ||
                    empty($field['type'])
                ) {
                    continue;
                }

                if ($field['type'] === 'file') {

                    if (
                        empty(trim($field['allowed_file_types'] ?? '')) ||
                        empty(trim($field['allowed_mimes'] ?? ''))
                    ) {
                        wp_send_json_error([
                            'message' =>
                                'File field "' . $field['label'] .
                                '" requires Allowed File Types and Allowed MIME Types.'
                        ]);
                    }
                }

                /*
                 * Sanitize
                 */
                $field['label'] = FormNova_Sanitizer::text($field['label']);
                $field['name'] = FormNova_Sanitizer::key($field['name']);
                $field['type'] = FormNova_Sanitizer::text($field['type']);

                /*
                 * Type whitelist
                 */
                if (
                    !in_array(
                        $field['type'],
                        $allowed_types,
                        true
                    )
                ) {
                    continue;
                }

                /*
                 * Reserved field names
                 */
                $reserved = [

                    'id',
                    'form_id',
                    'action',
                    'submit',
                    'nonce',
                    'post_id'
                ];

                if (
                    in_array(
                        $field['name'],
                        $reserved,
                        true
                    )
                ) {

                    wp_send_json_error([
                        'message' =>
                            'Reserved field name: ' .
                            $field['name']
                    ]);
                }

                /*
                 * Field name pattern
                 */
                if (
                    !preg_match(
                        '/^[a-z][a-z0-9_]*$/',
                        $field['name']
                    )
                ) {

                    wp_send_json_error([
                        'message' =>
                            'Invalid field name: ' .
                            $field['name']
                    ]);
                }

                /*
                 * Duplicate names
                 */
                if (
                    in_array(
                        $field['name'],
                        $used_names,
                        true
                    )
                ) {

                    wp_send_json_error([
                        'message' =>
                            'Duplicate field name: ' .
                            $field['name']
                    ]);
                }

                $used_names[] = $field['name'];

                /*
                 * Select / Radio / Checkbox
                 * must have options
                 */
                if (
                    in_array(
                        $field['type'],
                        ['select', 'radio', 'checkbox'],
                        true
                    )
                ) {

                    if (
                        empty(
                        trim(
                            $field['options'] ?? ''
                        )
                    )
                    ) {

                        wp_send_json_error([
                            'message' =>
                                'Options required for ' .
                                $field['label']
                        ]);
                    }
                }

                /*
                 * Save
                 */
                $field['form_id'] = $form_id;
                $field['sort_order'] = $index;

                $field_id = absint(
                    $field['id'] ?? 0
                );

                $deleted_fields = json_decode(
                    sanitize_text_field(
                        wp_unslash($_POST['deleted_fields'] ?? '[]')
                    ),
                    true
                );

                if (!empty($deleted_fields)) {

                    foreach ($deleted_fields as $deleted_id) {

                        $deleted_id = absint($deleted_id);

                        if ($deleted_id) {

                            $this->field_model->delete($deleted_id);
                        }
                    }
                }

                if ($field_id > 0) {

                    $this->field_model->update(
                        $field_id,
                        $field
                    );

                } else {

                    $field_id =
                        $this->field_model->create(
                            $field
                        );
                }

                if ($field_id) {

                    $existing_ids[] =
                        $field_id;
                }

                if (
                    $field_id &&
                    $field['type'] === 'file'
                ) {

                    $field_meta_model->save(
                        $field_id,
                        [
                            'allowed_file_types' =>
                                sanitize_text_field(
                                    $field['allowed_file_types']
                                    ?? 'jpg,jpeg,png,pdf'
                                ),

                            'allowed_mimes' =>
                                sanitize_text_field(
                                    $field['allowed_mimes']
                                    ?? ''
                                ),

                            'max_file_size' =>
                                absint(
                                    $field['max_file_size']
                                    ?? 5
                                )
                        ]
                    );
                }

                if (!$field_id) {
                    return new WP_Error(
                        'formnova_field_insert_failed',
                        __('Field insert failed.', 'formnova-form')
                    );
                }
            }

            wp_send_json_success([
                'form_id' => $form_id,
                'message' => 'Form saved successfully'
            ]);

        } catch (Exception $e) {
            wp_send_json_error([
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Edit form
     */
    public function edit_form_page()
    {
        $id = isset($_GET['id'])
            ? absint(wp_unslash($_GET['id']))
            : 0;

        if (
            !$id ||
            !isset($_GET['_wpnonce']) ||
            !wp_verify_nonce(
                sanitize_text_field(
                    wp_unslash($_GET['_wpnonce'])
                ),
                'formnova_edit_' . $id
            )
        ) {
            wp_die(
                esc_html__(
                    'Security check failed.',
                    'formnova-form'
                )
            );
        }

        if (!$id) {

            wp_die(
                esc_html__(
                    'Invalid form ID',
                    'formnova-form'
                )
            );

        }

        /*
         * Load Form
         */
        $formnova_form  = $this->form_model->get($id);

        if (!$formnova_form ) {

            wp_die(
                esc_html__(
                    'Form not found',
                    'formnova-form'
                )
            );

        }

        /*
         * Settings Tab
         */
        if (
            isset($_GET['tab']) &&
            sanitize_key($_GET['tab']) === 'settings'
        ) {

            $settings = [];

            if (
                method_exists(
                    $this->form_model,
                    'get_settings'
                )
            ) {

                $settings =
                    $this->form_model->get_settings(
                        $formnova_form ->id
                    );

                if (!is_array($settings)) {
                    $settings = [];
                }
            }

            require FORMNOVA_PATH .
                'app/Views/admin/form-settings.php';

            return;
        }

        /*
         * formnova_fields
         */
        $field_model = new FormNova_Field_Model();

        $formnova_fields =
            $field_model->get_by_form(
                $formnova_form ->id
            );

        if (!is_array($formnova_fields)) {
            $formnova_fields = [];
        }

        /*
         * Builder View
         */
        $this->view(
            'admin/form-builder',
            [
                'formnova_form' => $formnova_form ,
                'formnova_fields' => $formnova_fields,
            ]
        );
    }

    public function handle_delete_form()
    {
        if (!current_user_can('manage_options')) {
            wp_send_json_error([
                'message' => 'Unauthorized'
            ]);
        }

        check_admin_referer(
            'formnova_delete_form'
        );

        $id = isset($_GET['id'])
            ? absint($_GET['id'])
            : 0;

        if (!$id) {
            wp_die(esc_html__('Invalid form ID', 'formnova-form'));
        }

        $this->form_model->delete($id);

        wp_safe_redirect(

            admin_url(
                'admin.php?page=formnova'
            )

        );

        exit;
    }

    public function enqueue_assets()
    {
        wp_enqueue_script(
            'jquery-ui-sortable'
        );

        wp_enqueue_style(
            'formnova-builder',
            FORMNOVA_URL .
            'assets/css/form-builder.css',
            [],
            FORMNOVA_VERSION
        );

        wp_enqueue_script(
            'formnova-builder',
            FORMNOVA_URL .
            'assets/js/form-builder.js',
            [
                'jquery',
                'jquery-ui-sortable'
            ],
            FORMNOVA_VERSION,
            true
        );

        wp_localize_script(
            'formnova-builder',
            'formnovaAdmin',
            [
                'ajax_url' => admin_url(
                    'admin-ajax.php'
                ),

                'nonce' => wp_create_nonce(
                    'formnova_ajax_nonce'
                ),

                'edit_url' => admin_url(
                    'admin.php?page=formnova-add'
                ),
                'edit_nonce' => wp_create_nonce('formnova_edit_nonce')
            ]
        );
    }

    public function submissions_page()
    {
        $table =
            new FormNova_SubmissionListTable();

        $table->prepare_items();

        require FORMNOVA_PATH .
            'app/Views/admin/submissions-list.php';
    }

    public function submission_view_page()
    {
        require FORMNOVA_PATH . 'app/Views/admin/submission-view.php';
    }

    public function dashboard_page()
    {
        $controller =
            new FormNova_DashboardController();

        $controller->index();
    }

    public function settings_page()
    {
        $controller = new FormNova_Settings_Controller();

        $controller->settings_page();
    }
}