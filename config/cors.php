<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => [env('FRONTEND_URL', 'http://localhost:8080')],
    'allowed_origins_patterns' => in_array(env('APP_ENV', 'production'), ['local', 'testing'], true)
        ? ['#^https?://(localhost|127\.0\.0\.1)(:\d+)?$#']
        : [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => false,
];
