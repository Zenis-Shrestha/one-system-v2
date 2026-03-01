<?php

return [
    /*
    |--------------------------------------------------------------------------
    | CAS Server Configuration - Updated Architecture
    |--------------------------------------------------------------------------
    */
    'server_url' => env('CAS_SERVER_URL', 'https://your-cas-server.com'),
    'client_id' => env('CAS_CLIENT_ID'),
    'client_secret' => env('CAS_CLIENT_SECRET'),
    
    /*
    |--------------------------------------------------------------------------
    | Enhanced Authentication Settings
    |--------------------------------------------------------------------------
    */
    'token_expiry' => env('CAS_TOKEN_EXPIRY', 3600), // 1 hour
    'signature_validation' => env('CAS_SIGNATURE_VALIDATION', true),
    'hmac_algorithm' => 'sha256', // HMAC-SHA256 for enhanced security
    
    /*
    |--------------------------------------------------------------------------
    | New Route Configuration - Organized Structure
    |--------------------------------------------------------------------------
    */
    'routes' => [
        'sso_token' => '/api/sso/token',           // Enhanced client credentials flow
        'sso_validate' => '/api/sso/validate',     // Token validation endpoint
        'callback' => '/auth/sso/callback',        // SSO callback handler
        'user_dashboard' => '/user/dashboard',     // User dashboard route
        'logout' => '/auth/logout',                // Logout endpoint
    ],
    
    /*
    |--------------------------------------------------------------------------
    | PostgreSQL Schema Configuration
    |--------------------------------------------------------------------------
    */
    'database' => [
        'connection' => env('CAS_DB_CONNECTION', 'cas_system'),
        'schemas' => [
            'admin' => 'cas_admin',
            'user' => 'cas_user', 
            'public' => 'cas_public',
            'audit' => 'cas_audit'
        ]
    ],
    
    /*
    |--------------------------------------------------------------------------
    | IP Whitelist & Security Settings
    |--------------------------------------------------------------------------
    */
    'security' => [
        'ip_whitelist_enabled' => env('CAS_IP_WHITELIST', true),
        'audit_logging' => env('CAS_AUDIT_LOGGING', true),
        'rate_limiting' => env('CAS_RATE_LIMITING', true),
    ]
];