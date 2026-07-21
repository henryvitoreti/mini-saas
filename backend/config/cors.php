<?php

$allowedOrigins = array_filter(array_map(
    static fn (string $origin): string => trim($origin),
    explode(',', env('CORS_ALLOWED_ORIGINS', 'http://app.127.0.0.1.sslip.io:8081,http://app.127.0.0.1.sslip.io:3000'))
));

$allowedOriginPatterns = array_filter(array_map(
    static fn (string $originPattern): string => trim($originPattern),
    explode(',', env('CORS_ALLOWED_ORIGIN_PATTERNS', '~^https?://([a-z0-9-]+\.)?app\.127\.0\.0\.1\.sslip\.io(:[0-9]+)?$~'))
));

return [
    'paths' => ['api/*'],
    'allowed_methods' => ['*'],
    'allowed_origins' => $allowedOrigins,
    'allowed_origins_patterns' => $allowedOriginPatterns,
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => false,
];
