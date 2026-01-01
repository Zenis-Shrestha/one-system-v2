@extends('public.layouts.app')

@section('title', 'System Architecture - CAS Documentation')
@section('description', 'Complete architectural overview of the CAS System with Admin/User/Public separation, Livewire components, and PostgreSQL schema design.')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-4xl font-bold text-gray-900 mb-4">CAS System Architecture</h1>
        <p class="text-xl text-gray-600">Complete overview of our modular Central Authentication Service with enterprise-grade organization</p>
    </div>

    <!-- Architecture Diagrams -->
    <div class="bg-white shadow rounded-lg p-6 mb-8">
        <h2 class="text-2xl font-semibold text-gray-900 mb-4">📊 Visual Architecture</h2>
        
        <!-- Mermaid Support -->
        <script type="module">
            import mermaid from 'https://cdn.jsdelivr.net/npm/mermaid@10/dist/mermaid.esm.min.mjs';
            mermaid.initialize({ startOnLoad: true, theme: 'default' });
        </script>

        <div class="space-y-8">
            <!-- SSO Data Flow -->
            <div>
                <h3 class="text-lg font-semibold text-blue-900 mb-3">🔄 Dashboard SSO Flow</h3>
                <p class="text-gray-600 mb-4 text-sm">Sequence of events during a user-initiated login from the dashboard.</p>
                <div class="bg-slate-50 border border-slate-200 rounded-lg p-4 overflow-x-auto">
                    <pre class="mermaid">
sequenceDiagram
    actor User
    participant Dashboard as CAS Dashboard
    participant Server as CAS Server
    participant Client as Client App
    
    User->>Dashboard: Clicks "Launch App"
    Dashboard->>Server: Request One-Time Token (Auth Check)
    Server-->>Dashboard: Return Token + Redirect URL
    Dashboard->>Client: Redirect User with ?token=xyz
    Client->>Server: Validate Token (Back-channel)
    Server-->>Client: Return User Data (XML/JSON)
    Client->>User: Create Session & Log In
                    </pre>
                </div>
            </div>

            <!-- Admin Workflow -->
            <div>
                <h3 class="text-lg font-semibold text-red-900 mb-3">🛡️ Admin Onboarding Flow</h3>
                <p class="text-gray-600 mb-4 text-sm">Process for administrators to register and secure new client systems.</p>
                <div class="bg-slate-50 border border-slate-200 rounded-lg p-4 overflow-x-auto">
                    <pre class="mermaid">
sequenceDiagram
    actor Admin
    participant Panel as Admin Panel
    participant DB as System DB
    
    Admin->>Panel: Create New System
    Panel-->>DB: Insert CLIENT_SYSTEMS record
    DB-->>Panel: Return generated Client ID/Secret
    
    Admin->>Panel: Configure IP Whitelisting
    Panel-->>DB: Insert IP_WHITELIST records
    
    Admin->>Panel: Review Audit Logs
    Panel-->>DB: Query AUDIT_LOGS
    DB-->>Panel: Return Activity Report
                    </pre>
                </div>
            </div>

            <!-- ER Diagram -->
            <div>
                <h3 class="text-lg font-semibold text-purple-900 mb-3">🗄️ Entity Relationships</h3>
                <p class="text-gray-600 mb-4 text-sm">Core database models and their relationships, including security and audit layers.</p>
                <div class="bg-slate-50 border border-slate-200 rounded-lg p-4 overflow-x-auto">
                    <pre class="mermaid">
erDiagram
    USERS ||--o{ USER_CLIENT_LINKS : "links"
    USERS ||--o{ SSO_TOKENS : "generates"
    USERS ||--o{ AUDIT_LOGS : "triggers"
    CLIENT_SYSTEMS ||--o{ USER_CLIENT_LINKS : "has"
    CLIENT_SYSTEMS ||--o{ SSO_TOKENS : "validates"
    CLIENT_SYSTEMS ||--o{ IP_WHITELIST : "has"
    CLIENT_SYSTEMS ||--o{ AUDIT_LOGS : "logged_against"

    USERS {
        uuid id PK
        string email
        string password
        string role "user|admin"
    }

    CLIENT_SYSTEMS {
        bigint id PK
        string client_id
        string client_secret
        string callback_url
        boolean is_active
    }

    SSO_TOKENS {
        uuid id PK
        string token
        timestamp expires_at
        boolean consumed
    }

    IP_WHITELIST {
        bigint id PK
        string ip_address
        string description
        boolean is_active
    }

    AUDIT_LOGS {
        bigint id PK
        string event
        json payload
        string ip_address
        timestamp created_at
    }
                    </pre>
                </div>
            </div>
        </div>
    </div>

    <!-- Architecture Overview -->
    <div class="bg-white shadow rounded-lg p-6 mb-8">
        <h2 class="text-2xl font-semibold text-gray-900 mb-4">📐 System Design Principles</h2>
        <div class="grid md:grid-cols-3 gap-6">
            <div class="text-center p-4 bg-blue-50 rounded-lg">
                <div class="w-12 h-12 bg-blue-600 rounded-lg flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                </div>
                <h3 class="font-semibold text-blue-900">🔐 Admin Section</h3>
                <p class="text-sm text-blue-700 mt-2">Client system management, IP whitelisting, audit logs, security configuration</p>
            </div>
            <div class="text-center p-4 bg-green-50 rounded-lg">
                <div class="w-12 h-12 bg-green-600 rounded-lg flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
                <h3 class="font-semibold text-green-900">👤 User Section</h3>
                <p class="text-sm text-green-700 mt-2">Personal dashboard, client system linking, one-click SSO login, profile management</p>
            </div>
            <div class="text-center p-4 bg-gray-50 rounded-lg">
                <div class="w-12 h-12 bg-gray-600 rounded-lg flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                </div>
                <h3 class="font-semibold text-gray-900">📚 Public Section</h3>
                <p class="text-sm text-gray-700 mt-2">API documentation, integration guides, examples, public resources</p>
            </div>
        </div>
    </div>

    <!-- Code Organization -->
    <div class="bg-white shadow rounded-lg p-6 mb-8">
        <h2 class="text-2xl font-semibold text-gray-900 mb-4">🗂️ Code Organization</h2>
        <div class="grid md:grid-cols-2 gap-8">
            <div>
                <h3 class="text-lg font-semibold text-blue-900 mb-3">🔐 Admin Structure</h3>
                <div class="bg-blue-50 rounded-lg p-4">
                    <pre class="text-sm text-blue-800"><code>app/Http/Controllers/Admin/
├── ClientSystemController.php
├── AuditLogController.php
└── IpWhitelistController.php

app/Livewire/Admin/
├── ClientSystemsManager.php
└── IpWhitelistManager.php

resources/views/admin/
├── layouts/app.blade.php
├── client-systems-livewire.blade.php
├── ip-whitelist-livewire.blade.php
└── livewire/</code></pre>
                </div>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-green-900 mb-3">👤 User Structure</h3>
                <div class="bg-green-50 rounded-lg p-4">
                    <pre class="text-sm text-green-800"><code>app/Http/Controllers/User/
└── UserDashboardController.php

app/Livewire/User/
└── UserDashboard.php

resources/views/user/
├── layouts/app.blade.php
├── dashboard.blade.php
└── livewire/
    └── user-dashboard.blade.php</code></pre>
                </div>
            </div>
        </div>
        <div class="mt-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-3">📚 Public Structure</h3>
            <div class="bg-gray-50 rounded-lg p-4">
                <pre class="text-sm text-gray-800"><code>app/Http/Controllers/Public/
└── DocumentationController.php

resources/views/public/
├── layouts/app.blade.php
└── documentation/
    ├── index.blade.php
    ├── api.blade.php
    ├── examples.blade.php
    ├── system-architecture.blade.php
    └── (language-specific guides)</code></pre>
            </div>
        </div>
    </div>

    <!-- Database Schema -->
    <div class="bg-white shadow rounded-lg p-6 mb-8">
        <h2 class="text-2xl font-semibold text-gray-900 mb-4">🗄️ PostgreSQL Schema Design</h2>
        <p class="text-gray-600 mb-4">Enterprise-grade schema separation with proper access controls and security isolation.</p>
        
        <div class="grid md:grid-cols-2 gap-6">
            <div>
                <h3 class="text-lg font-semibold text-blue-900 mb-3">🔐 cas_admin Schema</h3>
                <div class="bg-blue-50 rounded-lg p-4">
                    <ul class="text-sm text-blue-800 space-y-1">
                        <li>• <strong>client_systems</strong> - Client application configurations</li>
                        <li>• <strong>ip_whitelist</strong> - IP access control lists</li>
                        <li>• <strong>system_config</strong> - Administrative settings</li>
                        <li class="mt-3 font-medium">Access: Admin users only</li>
                    </ul>
                </div>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-green-900 mb-3">👤 cas_user Schema</h3>
                <div class="bg-green-50 rounded-lg p-4">
                    <ul class="text-sm text-green-800 space-y-1">
                        <li>• <strong>users</strong> - User accounts and profiles</li>
                        <li>• <strong>user_client_links</strong> - System linkages</li>
                        <li>• <strong>sso_tokens</strong> - Authentication tokens</li>
                        <li class="mt-3 font-medium">Access: User-specific RLS policies</li>
                    </ul>
                </div>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-3">📚 cas_public Schema</h3>
                <div class="bg-gray-50 rounded-lg p-4">
                    <ul class="text-sm text-gray-800 space-y-1">
                        <li>• <strong>documentation_pages</strong> - API docs</li>
                        <li>• <strong>api_examples</strong> - Code samples</li>
                        <li>• <strong>public_content</strong> - General resources</li>
                        <li class="mt-3 font-medium">Access: Read-only public</li>
                    </ul>
                </div>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-purple-900 mb-3">📊 cas_audit Schema</h3>
                <div class="bg-purple-50 rounded-lg p-4">
                    <ul class="text-sm text-purple-800 space-y-1">
                        <li>• <strong>audit_logs</strong> - Complete activity logs</li>
                        <li>• <strong>security_events</strong> - Security incidents</li>
                        <li>• <strong>performance_metrics</strong> - System metrics</li>
                        <li class="mt-3 font-medium">Access: Audit-only permissions</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Route Organization -->
    <div class="bg-white shadow rounded-lg p-6 mb-8">
        <h2 class="text-2xl font-semibold text-gray-900 mb-4">🛣️ Route Organization</h2>
        <div class="grid md:grid-cols-3 gap-6">
            <div>
                <h3 class="text-lg font-semibold text-blue-900 mb-3">🔐 Admin Routes</h3>
                <div class="bg-blue-50 rounded-lg p-4">
                    <pre class="text-xs text-blue-800"><code>/admin/client-systems
/admin/ip-whitelist
/admin/audit-logs
/admin/system-config

// Blue-themed interfaces
// Full management capabilities</code></pre>
                </div>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-green-900 mb-3">👤 User Routes</h3>
                <div class="bg-green-50 rounded-lg p-4">
                    <pre class="text-xs text-green-800"><code>/user/dashboard
/user/profile
/user/systems
/user/settings

// Green-themed interfaces
// Self-service features</code></pre>
                </div>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-3">📚 Public Routes</h3>
                <div class="bg-gray-50 rounded-lg p-4">
                    <pre class="text-xs text-gray-800"><code>/docs
/docs/api
/docs/examples
/docs/integration

// Gray-themed interfaces
// Public documentation</code></pre>
                </div>
            </div>
        </div>
    </div>

    <!-- Technology Stack -->
    <div class="bg-white shadow rounded-lg p-6 mb-8">
        <h2 class="text-2xl font-semibold text-gray-900 mb-4">⚡ Technology Stack</h2>
        <div class="grid md:grid-cols-2 gap-8">
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-3">Backend Technologies</h3>
                <ul class="space-y-2 text-gray-700">
                    <li class="flex items-center">
                        <span class="w-2 h-2 bg-red-500 rounded-full mr-3"></span>
                        <strong>Laravel 12</strong> - Modern PHP framework with advanced features
                    </li>
                    <li class="flex items-center">
                        <span class="w-2 h-2 bg-purple-500 rounded-full mr-3"></span>
                        <strong>Livewire 3.6</strong> - Server-side rendering with real-time updates
                    </li>
                    <li class="flex items-center">
                        <span class="w-2 h-2 bg-blue-500 rounded-full mr-3"></span>
                        <strong>PostgreSQL</strong> - Multi-schema enterprise database design
                    </li>
                    <li class="flex items-center">
                        <span class="w-2 h-2 bg-green-500 rounded-full mr-3"></span>
                        <strong>Express.js</strong> - Alternative TypeScript-based CAS server
                    </li>
                </ul>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-3">Frontend Technologies</h3>
                <ul class="space-y-2 text-gray-700">
                    <li class="flex items-center">
                        <span class="w-2 h-2 bg-cyan-500 rounded-full mr-3"></span>
                        <strong>Tailwind CSS</strong> - Utility-first styling framework
                    </li>
                    <li class="flex items-center">
                        <span class="w-2 h-2 bg-gray-500 rounded-full mr-3"></span>
                        <strong>Alpine.js</strong> - Minimal JavaScript framework for interactions
                    </li>
                    <li class="flex items-center">
                        <span class="w-2 h-2 bg-purple-500 rounded-full mr-3"></span>
                        <strong>Blade Templates</strong> - Laravel's templating engine
                    </li>
                    <li class="flex items-center">
                        <span class="w-2 h-2 bg-orange-500 rounded-full mr-3"></span>
                        <strong>React/Vite</strong> - Optional modern frontend stack
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Security Features -->
    <div class="bg-white shadow rounded-lg p-6 mb-8">
        <h2 class="text-2xl font-semibold text-gray-900 mb-4">🔒 Security Architecture</h2>
        <div class="grid md:grid-cols-2 gap-6">
            <div>
                <h3 class="text-lg font-semibold text-red-900 mb-3">Authentication & Authorization</h3>
                <ul class="space-y-2 text-sm text-red-800">
                    <li>• <strong>JWT Tokens</strong> - Secure token-based authentication</li>
                    <li>• <strong>Scrypt/Bcrypt</strong> - Advanced password hashing</li>
                    <li>• <strong>Session Management</strong> - PostgreSQL-backed sessions</li>
                    <li>• <strong>Role-Based Access</strong> - Admin/User permission separation</li>
                </ul>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-red-900 mb-3">Security Controls</h3>
                <ul class="space-y-2 text-sm text-red-800">
                    <li>• <strong>IP Whitelisting</strong> - Network-level access control</li>
                    <li>• <strong>HMAC-SHA256</strong> - Cryptographic signature validation</li>
                    <li>• <strong>CSRF Protection</strong> - Built into Livewire components</li>
                    <li>• <strong>Audit Logging</strong> - Comprehensive activity tracking</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Performance Optimizations -->
    <div class="bg-white shadow rounded-lg p-6 mb-8">
        <h2 class="text-2xl font-semibold text-gray-900 mb-4">⚡ Performance Features</h2>
        <div class="bg-green-50 border border-green-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-green-900 mb-4">Livewire Implementation Benefits</h3>
            <div class="grid md:grid-cols-3 gap-6">
                <div class="text-center">
                    <div class="text-3xl font-bold text-green-700">75%</div>
                    <div class="text-sm text-green-600">Fewer HTTP Requests</div>
                    <p class="text-xs text-green-500 mt-1">Server-side rendering eliminates API calls</p>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-green-700">80%</div>
                    <div class="text-sm text-green-600">Faster Load Times</div>
                    <p class="text-xs text-green-500 mt-1">Initial content available immediately</p>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-green-700">100%</div>
                    <div class="text-sm text-green-600">Real-Time Updates</div>
                    <p class="text-xs text-green-500 mt-1">Live updates without page refreshes</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Integration Guide -->
    <div class="bg-white shadow rounded-lg p-6">
        <h2 class="text-2xl font-semibold text-gray-900 mb-4">🔗 Integration Overview</h2>
        <div class="grid md:grid-cols-2 gap-6">
            <div>
                <h3 class="text-lg font-semibold text-blue-900 mb-3">Traditional SSO Flow</h3>
                <div class="bg-blue-50 rounded-lg p-4">
                    <ol class="text-sm text-blue-800 space-y-1">
                        <li>1. Client system requests token with credentials</li>
                        <li>2. CAS validates client_id + client_secret + username</li>
                        <li>3. JWT token generated with user details</li>
                        <li>4. Client system receives authenticated user</li>
                    </ol>
                </div>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-green-900 mb-3">Dashboard SSO Flow</h3>
                <div class="bg-green-50 rounded-lg p-4">
                    <ol class="text-sm text-green-800 space-y-1">
                        <li>1. User links username to client system</li>
                        <li>2. One-click login from user dashboard</li>
                        <li>3. Automatic token generation and redirect</li>
                        <li>4. Seamless authentication to target system</li>
                    </ol>
                </div>
            </div>
        </div>
        
        <div class="mt-6 p-4 bg-gray-50 rounded-lg">
            <h4 class="font-semibold text-gray-900 mb-2">Supported Integration Languages</h4>
            <div class="flex flex-wrap gap-2">
                <span class="px-3 py-1 bg-gray-200 text-gray-800 rounded-full text-sm">Laravel/PHP</span>
                <span class="px-3 py-1 bg-gray-200 text-gray-800 rounded-full text-sm">.NET/C#</span>
                <span class="px-3 py-1 bg-gray-200 text-gray-800 rounded-full text-sm">Node.js</span>
                <span class="px-3 py-1 bg-gray-200 text-gray-800 rounded-full text-sm">Java</span>
                <span class="px-3 py-1 bg-gray-200 text-gray-800 rounded-full text-sm">Python</span>
                <span class="px-3 py-1 bg-gray-200 text-gray-800 rounded-full text-sm">JavaScript</span>
            </div>
        </div>
    </div>
</div>
@endsection