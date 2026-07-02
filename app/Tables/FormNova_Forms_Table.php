<?php

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class FormNova_Forms_Table extends WP_List_Table
{
    private $form_model;

    public function __construct()
    {
        parent::__construct([
            'singular' => 'form',
            'plural' => 'forms',
            'ajax' => false,
        ]);

        $this->form_model = new FormNova_Form_Model();
    }

    public function get_columns()
    {
        return [
            'cb' => '<input type="checkbox">',
            'title' => 'Title',
            'shortcode' => 'Shortcode',
            'author' => 'Author',
            'created_at' => 'Date',
        ];
    }

    public function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="forms[]" value="%d">',
            absint($item->id)
        );
    }

    public function column_title($item)
    {
        $edit_url = wp_nonce_url(
            admin_url(
                'admin.php?page=formnova-edit&id=' . absint($item->id)
            ),
            'formnova_edit_' . absint($item->id)
        );

        $actions = [
            'edit' => sprintf(
                '<a href="%s">Edit</a>',
                esc_url($edit_url)
            ),
        ];

        return sprintf(
            '<strong><a href="%s">%s</a></strong>%s',
            esc_url($edit_url),
            esc_html($item->title),
            $this->row_actions($actions)
        );
    }

    public function column_shortcode($item)
    {
        return sprintf(
            '<code>[formnova id="%d"]</code>',
            absint($item->id)
        );
    }

    public function column_default($item, $column_name)
    {
        return isset($item->$column_name)
            ? esc_html($item->$column_name)
            : '';
    }

    public function prepare_items()
    {
        $this->process_bulk_action();

        global $wpdb;

        $per_page = 20;
        $current_page = $this->get_pagenum();
        $offset = ($current_page - 1) * $per_page;

        $search = formnova_request('s');
        $orderby = formnova_request('orderby', 'get') ?: 'id';
        $order = strtoupper(
            formnova_request('order', 'get') ?: 'DESC'
        );

        $allowed_orderby = [
            'id',
            'title',
            'author',
            'created_at',
        ];

        if (!in_array($orderby, $allowed_orderby, true)) {
            $orderby = 'id';
        }

        if (!in_array($order, ['ASC', 'DESC'], true)) {
            $order = 'DESC';
        }

        $table = FormNova_Database::forms_table();
        $users_table = $wpdb->users;

        $where = '';
        $values = [];

        if (!empty($search)) {
            $total_items = (int) FormNova_Database::get_var(
                "SELECT COUNT(*)
                FROM %i f
                WHERE f.title LIKE %s",
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

        // Main items query
        $values[] = $per_page;
        $values[] = $offset;

        if (!empty($search)) {
            $this->items = FormNova_Database::get_results(
                "
                SELECT f.*, u.display_name AS author
                FROM %i f
                LEFT JOIN %i u
                ON u.ID = f.user_id
                WHERE f.title LIKE %s
                ORDER BY {$orderby} {$order}
                LIMIT %d OFFSET %d
                ",
                [
                    $table,
                    $users_table,
                    '%' . $wpdb->esc_like($search) . '%',
                    $per_page,
                    $offset
                ]
            );
        } else {
            $this->items = FormNova_Database::get_results(
                "
                SELECT f.*, u.display_name AS author
                FROM %i f
                LEFT JOIN %i u
                ON u.ID = f.user_id
                ORDER BY {$orderby} {$order}
                LIMIT %d OFFSET %d
                ",
                [
                    $table,
                    $users_table,
                    $per_page,
                    $offset
                ]
            );
        }

        $this->set_pagination_args([
            'total_items' => $total_items,
            'per_page' => $per_page,
            'total_pages' => ceil($total_items / $per_page),
        ]);

        $this->_column_headers = [
            $this->get_columns(),
            [],
            $this->get_sortable_columns(),
        ];
    }

    public function get_bulk_actions()
    {
        return [
            'delete' => 'Delete',
        ];
    }

    public function process_bulk_action()
    {
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

        $form_ids = isset($_POST['forms'])
            ? array_map(
                'absint',
                wp_unslash($_POST['forms'])
            )
            : [];

        if (empty($form_ids)) {
            return;
        }

        foreach ($form_ids as $form_id) {
            $this->form_model->delete($form_id);
        }
    }

    public function column_author($item)
    {
        return !empty($item->author)
            ? esc_html($item->author)
            : 'Unknown';
    }

    public function column_created_at($item)
    {
        if (empty($item->created_at)) {
            return '—';
        }

        return sprintf(
            '%s at %s',
            wp_date('Y/m/d', strtotime($item->created_at)),
            wp_date('g:i a', strtotime($item->created_at))
        );
    }

    public function get_sortable_columns()
    {
        return [
            'title' => ['title', true],
            'author' => ['author', false],
            'created_at' => ['created_at', false],
        ];
    }
}