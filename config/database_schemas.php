<?php

return [

    /*
    |--------------------------------------------------------------------------
    | PostgreSQL Schema Configuration
    |--------------------------------------------------------------------------
    |
    | This configuration defines the PostgreSQL schema organization for the
    | CAS system. Instead of using the default 'public' schema, we organize
    | tables into dedicated schemas for better security and maintainability.
    |
    */

    'default_connection' => 'pgsql',

    'schemas' => [

        /*
        |--------------------------------------------------------------------------
        | Admin Schema - Administrative Functions
        |--------------------------------------------------------------------------
        |
        | Contains tables for administrative functions like client system
        | management, IP whitelist, and system configuration.
        |
        */
        'admin' => [
            'name' => 'cas_admin',
            'description' => 'Administrative functions and management',
            'tables' => [
                'client_systems',
                'ip_whitelist',
                'system_config'
            ],
            'permissions' => [
                'cas_admin_role' => ['SELECT', 'INSERT', 'UPDATE', 'DELETE'],
                'cas_user_role' => ['SELECT']
            ]
        ],

        /*
        |--------------------------------------------------------------------------
        | User Schema - User-Facing Functions
        |--------------------------------------------------------------------------
        |
        | Contains tables for user management, authentication, and SSO tokens.
        |
        */
        'user' => [
            'name' => 'cas_user',
            'description' => 'User management and authentication',
            'tables' => [
                'users',
                'user_client_links',
                'sso_tokens'
            ],
            'permissions' => [
                'cas_admin_role' => ['SELECT', 'INSERT', 'UPDATE', 'DELETE'],
                'cas_user_role' => ['SELECT', 'INSERT', 'UPDATE']
            ]
        ],

        /*
        |--------------------------------------------------------------------------
        | Public Schema - Public Content
        |--------------------------------------------------------------------------
        |
        | Contains tables for documentation, examples, and other public content.
        |
        */
        'public' => [
            'name' => 'cas_public',
            'description' => 'Public documentation and content',
            'tables' => [
                'documentation_pages',
                'api_examples'
            ],
            'permissions' => [
                'cas_admin_role' => ['SELECT', 'INSERT', 'UPDATE', 'DELETE'],
                'cas_user_role' => ['SELECT'],
                'public' => ['SELECT']
            ]
        ],

        /*
        |--------------------------------------------------------------------------
        | Audit Schema - Logging and Security
        |--------------------------------------------------------------------------
        |
        | Contains tables for audit logging, security events, and compliance.
        |
        */
        'audit' => [
            'name' => 'cas_audit',
            'description' => 'Audit logging and security events',
            'tables' => [
                'audit_logs',
                'security_events'
            ],
            'permissions' => [
                'cas_admin_role' => ['SELECT', 'INSERT', 'UPDATE', 'DELETE'],
                'cas_audit_role' => ['SELECT', 'INSERT']
            ]
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Connection Configurations
    |--------------------------------------------------------------------------
    |
    | Define specific connection configurations for each schema. This allows
    | different parts of the application to connect with appropriate schemas.
    |
    */
    'connections' => [

        'cas_admin' => [
            'driver' => 'pgsql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'cas_system'),
            'username' => env('DB_USERNAME', 'cas_user'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'cas_admin,public',
            'sslmode' => 'prefer',
        ],

        'cas_user' => [
            'driver' => 'pgsql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'cas_system'),
            'username' => env('DB_USERNAME', 'cas_user'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'cas_user,public',
            'sslmode' => 'prefer',
        ],

        'cas_public' => [
            'driver' => 'pgsql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'cas_system'),
            'username' => env('DB_USERNAME', 'cas_user'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'cas_public,public',
            'sslmode' => 'prefer',
        ],

        'cas_audit' => [
            'driver' => 'pgsql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'cas_system'),
            'username' => env('DB_USERNAME', 'cas_user'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'cas_audit,public',
            'sslmode' => 'prefer',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Schema Migration Settings
    |--------------------------------------------------------------------------
    |
    | Settings for handling schema migrations and setup.
    |
    */
    'migration' => [
        'auto_create_schemas' => env('DB_AUTO_CREATE_SCHEMAS', true),
        'schema_setup_file' => database_path('schemas/postgresql_schema_setup.sql'),
        'run_schema_setup' => env('DB_RUN_SCHEMA_SETUP', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Model Schema Mapping
    |--------------------------------------------------------------------------
    |
    | Define which models belong to which schemas for automatic connection
    | and table resolution.
    |
    */
    'model_mapping' => [
        'App\Models\ClientSystem' => 'cas_admin',
        'App\Models\IpWhitelist' => 'cas_admin',
        'App\Models\SystemConfig' => 'cas_admin',

        'App\Models\User' => 'cas_user',
        'App\Models\UserClientLink' => 'cas_user',
        'App\Models\SsoToken' => 'cas_user',

        'App\Models\DocumentationPage' => 'cas_public',
        'App\Models\ApiExample' => 'cas_public',

        'App\Models\AuditLog' => 'cas_audit',
        'App\Models\SecurityEvent' => 'cas_audit',
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Policies
    |--------------------------------------------------------------------------
    |
    | Configuration for Row Level Security (RLS) policies.
    |
    */
    'security' => [
        'enable_rls' => env('DB_ENABLE_RLS', true),
        'policies' => [
            'admin_full_access' => [
                'role' => 'cas_admin_role',
                'tables' => ['cas_admin.*'],
                'permissions' => 'ALL'
            ],
            'user_own_data' => [
                'role' => 'cas_user_role',
                'tables' => ['cas_user.users', 'cas_user.user_client_links'],
                'condition' => 'user_id = current_user_id()'
            ],
            'public_read_only' => [
                'role' => 'public',
                'tables' => ['cas_public.*'],
                'permissions' => 'SELECT'
            ]
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Settings
    |--------------------------------------------------------------------------
    |
    | Performance-related configuration for schema usage.
    |
    */
    'performance' => [
        'enable_connection_pooling' => true,
        'connection_pool_size' => 10,
        'enable_query_cache' => true,
        'cache_ttl' => 300,
        'enable_prepared_statements' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Monitoring and Maintenance
    |--------------------------------------------------------------------------
    |
    | Configuration for monitoring and maintenance tasks.
    |
    */
    'maintenance' => [
        'auto_cleanup_tokens' => true,
        'token_cleanup_interval' => '1 hour',
        'audit_log_retention' => '90 days',
        'enable_statistics' => true,
        'statistics_update_interval' => '1 day',
    ]

];
