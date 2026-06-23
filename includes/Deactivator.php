<?php

if (!defined('ABSPATH')) {
    exit;
}

class FormNova_Deactivator
{
    public static function deactivate()
    {
        flush_rewrite_rules();
    }
}