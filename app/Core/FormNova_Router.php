<?php

if (!defined('ABSPATH')) {
    exit;
}

class FormNova_Router
{
    public function __construct()
    {
        $this->admin_routes();

        $this->frontend_routes();

        $this->post_routes();
    }

    /*
    |--------------------------------------------------------------------------
    | Admin Routes
    |--------------------------------------------------------------------------
    */
    private function admin_routes()
    {
        /*
         * Controllers
         */
        $admin_controller =
            new FormNova_AdminController();

        $field_controller =
            new FormNova_FieldController();

        $submission_admin =
            new FormNova_SubmissionAdminController();

        /*
         * Menu
         */
        add_action(
            'admin_menu',
            [
                $admin_controller,
                'register_menu'
            ]
        );

        /*
         * Assets
         */
        add_action(
            'admin_enqueue_scripts',
            [
                $admin_controller,
                'enqueue_assets'
            ]
        );

        add_action(
            'admin_init',
            [
                $admin_controller,
                'handle_requests'
            ]
        );

        add_action(
            'admin_post_formnova_delete',
            [
                $admin_controller,
                'handle_delete_form'
            ]
        );

        /*
         * Handle requests Field
        */

        add_action(
            'wp_ajax_formnova_delete_field',
            [
                $field_controller,
                'ajax_delete_field'
            ]
        );

        add_action(
            'wp_ajax_formnova_sort_fields',
            [
                $field_controller,
                'ajax_sort_fields'
            ]
        );

        add_action(
            'wp_ajax_formnova_save_form',
            [
                $admin_controller,
                'ajax_save_form'
            ]
        );

        /*
         * Handle requests Submission Admin
         */
        // add_action(
        //     'admin_init',
        //     [
        //         $submission_admin,
        //         'handle_requests'
        //     ]
        // );
    }

    /*
    |--------------------------------------------------------------------------
    | Frontend Routes
    |--------------------------------------------------------------------------
    */
    private function frontend_routes()
    {
        $submission_controller =
            new FormNova_SubmissionController();

        $frontend_controller =
            new FormNova_FrontendController();

        /*
         * Frontend Assets
         */
        add_action(
            'wp_enqueue_scripts',
            [
                $frontend_controller,
                'enqueue_assets'
            ]
        );

        /*
         * Form Submission
         */
        add_action(
            'wp_ajax_formnova_submit',
            [
                $submission_controller,
                'ajax_submit'
            ]
        );

        add_action(
            'wp_ajax_nopriv_formnova_submit',
            [
                $submission_controller,
                'ajax_submit'
            ]
        );

        /*
         * Shortcode
         */
        add_shortcode(
            'formnova',
            [
                $frontend_controller,
                'render_shortcode'
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Post Routes
    |--------------------------------------------------------------------------
    */
    private function post_routes()
    {
        $settings_controller =
            new FormNova_FormSettingsController();

        add_action(
            'admin_post_formnova_save_settings',
            [
                $settings_controller,
                'save'
            ]
        );
    }
}