<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Documentation') - CAS Authentication System</title>
    <meta name="description" content="@yield('description', 'Comprehensive documentation for CAS Single Sign-On authentication system')">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    
    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Scripts and Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        /* Documentation Specific Styles */
        .docs-sidebar {
            width: 280px;
            background: #f8fafc;
            border-right: 1px solid #e2e8f0;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 10;
            overflow-y: auto;
        }
        
        .docs-content {
            margin-left: 280px;
            min-height: 100vh;
        }
        
        .sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid #e2e8f0;
            background: white;
        }
        
        .sidebar-section {
            padding: 1rem 0;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .sidebar-section:last-child {
            border-bottom: none;
        }
        
        .sidebar-title {
            padding: 0 1.5rem 0.5rem 1.5rem;
            font-size: 0.875rem;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        .sidebar-link {
            display: flex;
            align-items: center;
            padding: 0.75rem 1.5rem;
            color: #475569;
            text-decoration: none;
            transition: all 0.2s;
            border-left: 3px solid transparent;
        }
        
        .sidebar-link:hover {
            background: #e2e8f0;
            color: #1e293b;
        }
        
        .sidebar-link.active {
            background: #dbeafe;
            color: #1d4ed8;
            border-left-color: #3b82f6;
            font-weight: 500;
        }
        
        .sidebar-icon {
            width: 20px;
            margin-right: 0.75rem;
            text-align: center;
        }
        
        .code-block {
            background: #1e293b;
            color: #e2e8f0;
            border-radius: 0.5rem;
            padding: 0.5rem; /* Reduced from 1rem */
            overflow-x: auto;
        }
        
        .code-block pre {
            margin: 0;
            padding: 0.5rem; /* moved inner padding to pre if needed, or keep 0 */
            font-family: 'JetBrains Mono', 'Fira Code', Consolas, monospace;
            font-size: 0.875rem;
            line-height: 1.5;
        }
        
        /* Mobile responsiveness */
        @media (max-width: 768px) {
            .docs-sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s;
            }
            
            .docs-sidebar.mobile-open {
                transform: translateX(0);
            }
            
            .docs-content {
                margin-left: 0;
            }
            
            .mobile-menu-btn {
                position: fixed;
                top: 1rem;
                left: 1rem;
                z-index: 20;
                background: #3b82f6;
                color: white;
                border: none;
                border-radius: 0.5rem;
                padding: 0.75rem;
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            }
        }
        
        /* Language badges */
        .language-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.5rem;
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            border-radius: 0.375rem;
            font-size: 0.75rem;
            font-weight: 500;
            color: #475569;
            margin-left: auto;
        }
        
        .language-badge.popular {
            background: #dbeafe;
            border-color: #3b82f6;
            color: #1d4ed8;
        }
        
        .language-badge.new {
            background: #dcfce7;
            border-color: #22c55e;
            color: #15803d;
        }
    </style>
</head>
<body class="bg-white">
    <!-- Mobile Menu Button -->
    <button class="mobile-menu-btn md:hidden" onclick="toggleMobileSidebar()">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Documentation Sidebar -->
    <nav class="docs-sidebar" id="docs-sidebar">
        <!-- Header -->
        <div class="sidebar-header">
            <a href="{{ route('docs') }}" class="flex items-center">
                <i class="fas fa-shield-alt text-blue-600 text-xl mr-3"></i>
                <div>
                    <div class="font-bold text-gray-900">CAS Docs</div>
                    <div class="text-sm text-gray-500">v2.0 Enterprise</div>
                </div>
            </a>
        </div>

        <!-- Getting Started -->
        <div class="sidebar-section">
            <div class="sidebar-title">Getting Started</div>
            <a href="{{ route('docs') }}" class="sidebar-link {{ request()->routeIs('docs') ? 'active' : '' }}">
                <i class="fas fa-home sidebar-icon"></i>
                Overview
            </a>
            <a href="{{ route('docs.security') }}" class="sidebar-link {{ request()->routeIs('docs.security') ? 'active' : '' }}">
                <i class="fas fa-shield-alt sidebar-icon"></i>
                Security Features
                <span class="language-badge new">Enhanced</span>
            </a>
            <a href="{{ route('docs.api.overview') }}" class="sidebar-link {{ request()->routeIs('docs.api.overview') ? 'active' : '' }}">
                <i class="fas fa-code sidebar-icon"></i>
                API Reference
            </a>
            <a href="{{ route('docs.examples') }}" class="sidebar-link {{ request()->routeIs('docs.examples') ? 'active' : '' }}">
                <i class="fas fa-book-open sidebar-icon"></i>
                Examples
            </a>
        </div>

        <!-- How To Use -->
        <div class="sidebar-section">
            <div class="sidebar-title">How To Use</div>
            <a href="{{ route('docs.quick-start') }}" class="sidebar-link {{ request()->routeIs('docs.quick-start') ? 'active' : '' }}">
                <i class="fas fa-rocket sidebar-icon"></i>
                Quick Start
                <span class="language-badge new">New</span>
            </a>
            <a href="{{ route('docs.admin-panel') }}" class="sidebar-link {{ request()->routeIs('docs.admin-panel') ? 'active' : '' }}">
                <i class="fas fa-tachometer-alt sidebar-icon"></i>
                Admin Panel
            </a>
            <a href="{{ route('docs.client-registration') }}" class="sidebar-link {{ request()->routeIs('docs.client-registration') ? 'active' : '' }}">
                <i class="fas fa-plus-circle sidebar-icon"></i>
                Client Registration
            </a>
            <a href="{{ route('docs.user-management') }}" class="sidebar-link {{ request()->routeIs('docs.user-management') ? 'active' : '' }}">
                <i class="fas fa-users-cog sidebar-icon"></i>
                User Management
            </a>
            <a href="{{ route('docs.two-factor-auth') }}" class="sidebar-link {{ request()->routeIs('docs.two-factor-auth') ? 'active' : '' }}">
                <i class="fas fa-shield-alt sidebar-icon"></i>
                2FA Setup
            </a>
        </div>
        <div class="sidebar-section">
            <div class="sidebar-title">Programming Languages</div>
            
            <!-- Laravel -->
            <a href="{{ route('docs.laravel') }}" class="sidebar-link {{ request()->routeIs('docs.laravel') ? 'active' : '' }}">
                <i class="fab fa-laravel sidebar-icon text-red-500"></i>
                Laravel
                <span class="language-badge popular">Popular</span>
            </a>
            
            <!-- Node.js -->
            <a href="{{ route('docs.nodejs') }}" class="sidebar-link {{ request()->routeIs('docs.nodejs') ? 'active' : '' }}">
                <i class="fab fa-node-js sidebar-icon text-green-500"></i>
                Node.js
                <span class="language-badge popular">Popular</span>
            </a>
            
            <!-- JavaScript -->
            <a href="{{ route('docs.javascript') }}" class="sidebar-link {{ request()->routeIs('docs.javascript') ? 'active' : '' }}">
                <i class="fab fa-js-square sidebar-icon text-yellow-500"></i>
                JavaScript
            </a>
            
            <!-- Python -->
            <a href="{{ route('docs.python') }}" class="sidebar-link {{ request()->routeIs('docs.python') ? 'active' : '' }}">
                <i class="fab fa-python sidebar-icon text-blue-500"></i>
                Python
            </a>
            
            <!-- Java -->
            <a href="{{ route('docs.java') }}" class="sidebar-link {{ request()->routeIs('docs.java') ? 'active' : '' }}">
                <i class="fab fa-java sidebar-icon text-orange-500"></i>
                Java
            </a>
            
            <!-- .NET -->
            <a href="{{ route('docs.dotnet') }}" class="sidebar-link {{ request()->routeIs('docs.dotnet') ? 'active' : '' }}">
                <i class="fab fa-microsoft sidebar-icon text-blue-600"></i>
                .NET / C#
            </a>
        </div>

        <!-- Advanced Topics -->
        <div class="sidebar-section">
            <div class="sidebar-title">Advanced Topics</div>
            <a href="{{ route('docs.architecture') }}" class="sidebar-link {{ request()->routeIs('docs.architecture') ? 'active' : '' }}">
                <i class="fas fa-sitemap sidebar-icon"></i>
                System Architecture
            </a>
            <a href="/docs/authentication" class="sidebar-link {{ request()->is('docs/authentication') ? 'active' : '' }}">
                <i class="fas fa-key sidebar-icon"></i>
                Authentication Flows
            </a>
            <a href="{{ route('docs.deployment') }}" class="sidebar-link {{ request()->routeIs('docs.deployment') ? 'active' : '' }}">
                <i class="fas fa-server sidebar-icon"></i>
                Deployment Guide
            </a>
            <a href="{{ route('docs.troubleshooting') }}" class="sidebar-link {{ request()->routeIs('docs.troubleshooting') ? 'active' : '' }}">
                <i class="fas fa-tools sidebar-icon"></i>
                Troubleshooting
            </a>
        </div>

        <!-- Technical Reference -->
        <div class="sidebar-section">
            <div class="sidebar-title">Technical Reference</div>
            <a href="{{ route('docs.webhooks') }}" class="sidebar-link {{ request()->routeIs('docs.webhooks') ? 'active' : '' }}">
                <i class="fas fa-bolt sidebar-icon"></i>
                Webhooks
                <span class="language-badge new">New</span>
            </a>
            <a href="{{ route('docs.sdks') }}" class="sidebar-link {{ request()->routeIs('docs.sdks') ? 'active' : '' }}">
                <i class="fas fa-cube sidebar-icon"></i>
                SDKs &amp; Packages
            </a>

        </div>

        <!-- Resources -->
        <div class="sidebar-section">
            <div class="sidebar-title">Resources</div>
            <a href="/downloads/laravel-cas-client-package.zip" class="sidebar-link">
                <i class="fas fa-download sidebar-icon"></i>
                Download Packages
            </a>
            <a href="{{ url('/') }}" class="sidebar-link">
                <i class="fas fa-arrow-left sidebar-icon"></i>
                Back to CAS System
            </a>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="docs-content">
        <!-- Mobile Overlay -->
        <div class="md:hidden fixed inset-0 bg-black bg-opacity-50 z-5 hidden" id="mobile-overlay" onclick="toggleMobileSidebar()"></div>
        
        <!-- Page Header (if needed) -->
        @hasSection('page-header')
            <div class="bg-white border-b border-gray-200 px-6 py-4">
                @yield('page-header')
            </div>
        @endif

        <!-- Content -->
        <div class="p-6">
            @yield('content')
        </div>

        <!-- Footer -->
        <footer class="border-t border-gray-200 bg-gray-50 px-6 py-8 mt-12">
            <div class="max-w-4xl">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div>
                        <h3 class="font-semibold text-gray-900 mb-4">Documentation</h3>
                        <ul class="space-y-2 text-sm text-gray-600">
                            <li><a href="{{ route('docs') }}" class="hover:text-blue-600">Getting Started</a></li>
                            <li><a href="{{ route('docs.api.overview') }}" class="hover:text-blue-600">API Reference</a></li>
                            <li><a href="{{ route('docs.security') }}" class="hover:text-blue-600">Security Guide</a></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900 mb-4">Popular Integrations</h3>
                        <ul class="space-y-2 text-sm text-gray-600">
                            <li><a href="{{ route('docs.laravel') }}" class="hover:text-blue-600">Laravel Package</a></li>
                            <li><a href="{{ route('docs.nodejs') }}" class="hover:text-blue-600">Node.js SDK</a></li>
                            <li><a href="{{ route('docs.python') }}" class="hover:text-blue-600">Python Library</a></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900 mb-4">Support</h3>
                        <ul class="space-y-2 text-sm text-gray-600">
                            <li><a href="/docs/troubleshooting" class="hover:text-blue-600">Troubleshooting</a></li>
                            <li><a href="{{ route('docs.examples') }}" class="hover:text-blue-600">Code Examples</a></li>
                            <li><a href="{{ url('/') }}" class="hover:text-blue-600">CAS System</a></li>
                        </ul>
                    </div>
                </div>
                <div class="border-t border-gray-200 mt-8 pt-8 text-center text-sm text-gray-500">
                    <p>&copy; {{ date('Y') }} CAS Authentication System. Enterprise-grade security with comprehensive documentation.</p>
                </div>
            </div>
        </footer>
    </main>

    <script>
        function toggleMobileSidebar() {
            const sidebar = document.getElementById('docs-sidebar');
            const overlay = document.getElementById('mobile-overlay');
            
            sidebar.classList.toggle('mobile-open');
            overlay.classList.toggle('hidden');
        }
        
        // Close mobile sidebar when clicking on content area
        document.addEventListener('click', function(event) {
            if (window.innerWidth <= 768) {
                const sidebar = document.getElementById('docs-sidebar');
                const overlay = document.getElementById('mobile-overlay');
                
                if (!sidebar.contains(event.target) && !event.target.classList.contains('mobile-menu-btn')) {
                    sidebar.classList.remove('mobile-open');
                    overlay.classList.add('hidden');
                }
            }
        });
        
        // Handle responsive behavior on window resize
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768) {
                const sidebar = document.getElementById('docs-sidebar');
                const overlay = document.getElementById('mobile-overlay');
                
                sidebar.classList.remove('mobile-open');
                overlay.classList.add('hidden');
            }
        });
    </script>
</body>
</html>