<?php
/*
Plugin Name: FormNova – Drag & Drop Contact Form Builder
Plugin URI: https://github.com/nikdobs/formnova-form
Description: Build powerful WordPress contact forms with drag-and-drop builder, AJAX submissions, file uploads, custom fields, analytics, email notifications, and submission management.
Version: 1.0.0
Author: Nikunj Dobariya
Author URI: https://profiles.wordpress.org/nikdobs/
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: formnova-form
Domain Path: /languages
Requires at least: 6.7
Requires PHP: 7.4
*/

if (!defined('ABSPATH')) {
    exit;
}

/*
|--------------------------------------------------------------------------
| Constants
|--------------------------------------------------------------------------
*/

define(
    'FORMNOVA_PATH',
    plugin_dir_path(__FILE__)
);

define(
    'FORMNOVA_URL',
    plugin_dir_url(__FILE__)
);

define(
    'FORMNOVA_VERSION',
    '1.0.0'
);

/*
|--------------------------------------------------------------------------
| Load Core Classes
|--------------------------------------------------------------------------
*/

require_once FORMNOVA_PATH .
    'app/Core/FormNova_Autoloader.php';

require_once FORMNOVA_PATH .
    'app/Core/FormNova_Loader.php';

/*
|--------------------------------------------------------------------------
| Start Plugin
|--------------------------------------------------------------------------
*/

new FormNova_Loader();