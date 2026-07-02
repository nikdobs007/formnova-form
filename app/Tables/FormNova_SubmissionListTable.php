<?php

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class FormNova_SubmissionListTable extends WP_List_Table
{
    private $submission_model;

    public function __construct()
    {
        parent::__construct([
            'singular' => 'submission',
            'plural' => 'submissions',
            'ajax' => false
        ]);

        $this->submission_model = new FormNova_Submission_Model();
    }

    /*
     * Table Columns
     */
    public function get_columns()
    {
        return [
            'cb' => '<input type="checkbox">',
            'id' => 'Submission ID',
            'form_id' => 'Form ID',
            'submitted_at' => 'Date'
        ];
    }

    /*
     * Checkbox Column
     */
    public function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="submissions[]" value="%d">',
            $item->id
        );
    }

    /*
     * Submission ID Column
     */
    public function column_id($item)
    {
        $view_url = admin_url(
            'admin.php?page=formnova-submission-view&id=' . $item->id
        );

        $actions = [
            'view' => sprintf(
                '<a href="%s">View</a>',
                esc_url($view_url)
            ),
        ];

        return sprintf(
            '<strong>
            <a href="%s">#%d</a>
        </strong>%s',
            esc_url($view_url),
            absint($item->id),
            $this->row_actions($actions)
        );
    }

    /*
     * Form ID Column
     */
    public function column_form_id($item)
    {
        return absint($item->form_id);
    }

    /*
     * Date Column
     */
    public function column_submitted_at($item)
    {
        if (empty($item->submitted_at)) {
            return '—';
        }

        return sprintf(
            '%s at %s',
            wp_date(
                'Y/m/d',
                strtotime($item->submitted_at)
            ),
            wp_date(
                'g:i a',
                strtotime($item->submitted_at)
            )
        );
    }

    /*
     * Default Column
     */
    public function column_default($item, $column_name)
    {
        return $item->$column_name ?? '';
    }

    /*
     * Sortable Columns
     */
    public function get_sortable_columns()
    {
        return [
            'id' => ['id', true],
            'form_id' => ['form_id', false],
            'submitted_at' => ['submitted_at', false]
        ];
    }

    /*
     * Bulk Actions
     */
    public function get_bulk_actions()
    {
        return [
            'delete' => 'Delete'
        ];
    }

    /*
     * Process Bulk Actions
     */
    public function process_bulk_action()
    {
        if (!isset($_POST['formnova_bulk_nonce'])) {
            return;
        }

        if (
            !isset($_POST['formnova_bulk_nonce']) ||
            !wp_verify_nonce(
                sanitize_text_field(
                    wp_unslash($_POST['formnova_bulk_nonce'])
                ),
                'formnova_bulk_action'
            )
        ) {
            return;
        }

        if ('delete' !== $this->current_action()) {
            return;
        }

        $submissions = isset($_POST['submissions'])
            ? array_map(
                'absint',
                wp_unslash($_POST['submissions'])
            )
            : [];

        if (empty($submissions)) {
            return;
        }

        foreach ((array) wp_unslash($submissions) as $submission_id) {
            $this->submission_model->delete(
                absint($submission_id)
            );
        }
    }

    /*
     * Prepare Items
     */
    public function prepare_items()
    {
        $this->process_bulk_action();

        global $wpdb;

        $table = FormNova_Database::submissions_table();

        $per_page = 20;
        $current_page = $this->get_pagenum();

        $search = formnova_request('s');

        $orderby = formnova_request('orderby', 'get') ?: 'id';

        $order = strtoupper(
            formnova_request('order', 'get') ?: 'DESC'
        );

        $allowed_orderby = [
            'id',
            'form_id',
            'submitted_at'
        ];

        if (!in_array($orderby, $allowed_orderby, true)) {
            $orderby = 'id';
        }

        if (!in_array($order, ['ASC', 'DESC'], true)) {
            $order = 'DESC';
        }

        $where = '';

        $table_sql = esc_sql($table);

        if (!empty($search)) {
            $total_items = (int) FormNova_Database::get_var(
                "SELECT COUNT(*)
                FROM %i
                WHERE form_id LIKE %s",
                [
                    $table,
                    '%' . $wpdb->esc_like($search) . '%'
                ]
            );
        } else {
            $total_items = (int) FormNova_Database::get_var(
                "SELECT COUNT(*)
                FROM %i",
                [
                    $table
                ]
            );
        }

        $offset = ($current_page - 1) * $per_page;

        $allowed_orderby = ['id', 'submitted_at', 'form_id'];
        $allowed_order = ['ASC', 'DESC'];

        $orderby = in_array($orderby, $allowed_orderby, true)
            ? $orderby
            : 'id';

        $order = in_array(strtoupper($order), $allowed_order, true)
            ? strtoupper($order)
            : 'DESC';

        if (!empty($search)) {
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            $this->items = FormNova_Database::get_results(
                "
                SELECT *
                FROM %i
                WHERE form_id LIKE %s
                ORDER BY {$orderby} {$order}
                LIMIT %d OFFSET %d
                ",
                [
                    $table,
                    '%' . $wpdb->esc_like($search) . '%',
                    $per_page,
                    $offset
                ]
            );
        } else {
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            $this->items = FormNova_Database::get_results(
                "
                SELECT *
                FROM %i
                ORDER BY {$orderby} {$order}
                LIMIT %d OFFSET %d
                ",
                [
                    $table,
                    $per_page,
                    $offset
                ]
            );
        }

        $this->set_pagination_args([
            'total_items' => $total_items,
            'per_page' => $per_page,
            'total_pages' => ceil(
                $total_items / $per_page
            )
        ]);

        $this->_column_headers = [
            $this->get_columns(),
            [],
            $this->get_sortable_columns()
        ];
    }
}