<?php
/**
 * Symfony Application Entry Point for Hostinger
 * 
 * This file is the main entry point for all web requests.
 * Place this in public_html/ along with all other files
 */

require_once __DIR__.'/vendor/autoload.php';
require_once __DIR__.'/vendor/autoload_runtime.php';

use App\Kernel;

return function (array $context) {
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
