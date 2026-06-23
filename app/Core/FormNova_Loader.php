<?php

if (!defined('ABSPATH')) {
    exit;
}

class FormNova_Loader
{
    public function __construct()
    {
        require_once FORMNOVA_PATH . 'app/Helpers/helpers.php';
        /*
         * Load FormNova_Router
         */
        new FormNova_Router();
    }
}