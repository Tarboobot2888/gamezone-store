<?php

return [
    'default' => env('FIREBASE_PROJECT', 'app'),

    'projects' => [
        'app' => [
            'credentials' => [
                'file' => env('FIREBASE_CREDENTIALS'),
                'auto_discovery' => true,
            ],
            'auth' => [
                'tenant_id' => env('FIREBASE_AUTH_TENANT_ID'),
            ],
            'database' => [
                'url' => env('FIREBASE_DATABASE_URL'),
            ],
            'dynamic_links' => [
                'default_domain' => env('FIREBASE_DYNAMIC_LINKS_DEFAULT_DOMAIN'),
            ],
            'storage' => [
                'default_bucket' => env('FIREBASE_STORAGE_DEFAULT_BUCKET'),
            ],
            'cache_store' => env('FIREBASE_CACHE_STORE', 'file'),
            'logging' => [
                'http_debug_log_group' => null,
                'http_handler_stack' => null,
            ],
            'http_client_options' => [
                'proxy' => env('FIREBASE_HTTP_CLIENT_PROXY'),
                'timeout' => env('FIREBASE_HTTP_CLIENT_TIMEOUT'),
            ],
        ],
    ],
];
