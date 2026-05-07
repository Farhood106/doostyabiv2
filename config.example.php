<?php
return [
    'db' => [
        'host' => '127.0.0.1',
        'name' => 'doostyabi',
        'user' => 'doostyabi_user',
        'pass' => 'change-me',
        'charset' => 'utf8mb4',
    ],
    'app' => [
        'base_url' => '',
        'env' => 'production',
        'session_name' => 'doostyabi_session',
        // Set to a long random string temporarily to enable /install scripts in production.
        // Remove or set to null after setup.
        'install_token' => null,
    ],
];
