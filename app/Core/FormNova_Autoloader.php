<?php

if (!defined('ABSPATH')) {
    exit;
}

class FormNova_Autoloader
{
    public function __construct()
    {
        spl_autoload_register(
            [$this, 'autoload']
        );
    }

    public function autoload($class)
    {
        $folders = [

            'app/Controllers/',
            'app/Models/',
            'app/Helpers/',
            'app/Core/',
            'app/Views/',        
            'app/Tables/',
            'includes/'

        ];

        foreach ($folders as $folder) {

            $file =
                FORMNOVA_PATH .
                $folder .
                $class .
                '.php';

            if (
                file_exists($file)
            ) {

                require_once $file;

                return;
            }
        }
    }
}

/*
|--------------------------------------------------------------------------
| Register FormNova_Autoloader
|--------------------------------------------------------------------------
*/

new FormNova_Autoloader();