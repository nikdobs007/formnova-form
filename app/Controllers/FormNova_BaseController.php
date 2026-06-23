<?php

if (!defined('ABSPATH')) {
    exit;
}

class FormNova_BaseController
{
    protected function view(
        $view,
        $data = []
    ) {
        extract($data);

        require FORMNOVA_PATH .
            'app/Views/' .
            $view .
            '.php';
    }

    protected function redirect(
        $url
    ) {
        wp_safe_redirect($url);

        exit;
    }
}