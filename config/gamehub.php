<?php

return [
    'sandbox_domain' => env('GAME_SANDBOX_DOMAIN', ''),
    'max_zip_size_mb' => (int) env('GAME_MAX_ZIP_SIZE_MB', 50),
    'max_extracted_size_mb' => (int) env('GAME_MAX_EXTRACTED_SIZE_MB', 150),
    'max_file_count' => (int) env('GAME_MAX_FILE_COUNT', 500),
    'forbidden_extensions' => [
        'php', 'php3', 'php4', 'php5', 'php7', 'php8', 'phtml', 'phar',
        'exe', 'bat', 'cmd', 'sh', 'bash', 'cgi', 'pl', 'py', 'rb',
        'dll', 'so', 'dylib', 'msi', 'vbs', 'ps1', 'htaccess', 'htpasswd',
        'env', 'ini', 'config', 'jsp', 'asp', 'aspx',
    ],
    'seed_accounts' => [
        'super_admin' => [
            'email' => env('SEED_SUPER_ADMIN_EMAIL', 'admin@gamehub.local'),
            'password' => env('SEED_SUPER_ADMIN_PASSWORD', 'GameHubLocal2026!'),
        ],
        'admin' => [
            'email' => env('SEED_ADMIN_EMAIL', 'moderator@gamehub.local'),
            'password' => env('SEED_ADMIN_PASSWORD', 'GameHubAdmin2026!'),
        ],
        'user' => [
            'email' => env('SEED_USER_EMAIL', 'player@gamehub.local'),
            'password' => env('SEED_USER_PASSWORD', 'GameHubPlayer2026!'),
        ],
    ],
];

