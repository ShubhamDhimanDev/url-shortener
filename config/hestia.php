<?php

return [
    /*
    |--------------------------------------------------------------------------
    | HestiaCP Configuration
    |--------------------------------------------------------------------------
    |
    | Used by ProvisionDomainSslJob to add domain aliases and issue SSL
    | certificates via HestiaCP's command-line tools automatically.
    |
    */

    // The HestiaCP system user who owns the main web domain
    'user' => env('HESTIA_USER', 'powerhouse'),

    // Main domain the Laravel app lives under (the "parent" for aliases)
    'main_domain' => env('HESTIA_MAIN_DOMAIN', 'url.insanedev.in'),

    // Path to HestiaCP CLI binaries
    'bin_path' => env('HESTIA_BIN_PATH', '/usr/local/hestia/bin'),
];
