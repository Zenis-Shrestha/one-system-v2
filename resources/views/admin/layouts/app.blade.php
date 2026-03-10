<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'CAS Admin Panel')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    @livewireStyles

    <style>
        *, *::before, *::after { font-family: 'Inter', system-ui, -apple-system, sans-serif; }

        .loading-pulse {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: .5; }
        }

        .loading-overlay {
            backdrop-filter: blur(2px);
        }
    </style>


</head>
<body class="bg-gray-50 min-h-screen">
    <nav class="bg-blue-800 shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center">
                        <h1 class="text-xl font-bold text-white">CAS Admin</h1>
                    </div>
                    <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                        <a href="/admin/dashboard"
                           class="border-b-2 {{ request()->is('admin/dashboard*') || request()->is('admin') ? 'border-blue-300 text-white' : 'border-transparent text-blue-100 hover:text-white hover:border-blue-300' }} inline-flex items-center px-1 pt-1 text-sm font-medium transition-all duration-200 hover:scale-105">
                            <span class="flex items-center space-x-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v6H8V5z"></path>
                                </svg>
                                <span>Dashboard</span>
                            </span>
                        </a>
                        <a href="/admin/client-systems"
                           class="border-b-2 {{ request()->is('admin/client-systems*') ? 'border-blue-300 text-white' : 'border-transparent text-blue-100 hover:text-white hover:border-blue-300' }} inline-flex items-center px-1 pt-1 text-sm font-medium transition-all duration-200 hover:scale-105">
                            <span class="flex items-center space-x-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path>
                                </svg>
                                <span>Client Systems</span>
                            </span>
                        </a>
                        <a href="/admin/users"
                           class="border-b-2 {{ request()->is('admin/users*') ? 'border-blue-300 text-white' : 'border-transparent text-blue-100 hover:text-white hover:border-blue-300' }} inline-flex items-center px-1 pt-1 text-sm font-medium transition-all duration-200 hover:scale-105">
                            <span class="flex items-center space-x-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                                </svg>
                                <span>Users</span>
                            </span>
                        </a>
                        <a href="/admin/ip-whitelist"
                           class="border-b-2 {{ request()->is('admin/ip-whitelist*') ? 'border-blue-300 text-white' : 'border-transparent text-blue-100 hover:text-white hover:border-blue-300' }} inline-flex items-center px-1 pt-1 text-sm font-medium transition-all duration-200 hover:scale-105">
                            <span class="flex items-center space-x-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                </svg>
                                <span>IP Whitelist</span>
                            </span>
                        </a>
                        <a href="/admin/audit-logs"
                           class="border-b-2 {{ request()->is('admin/audit-logs*') ? 'border-blue-300 text-white' : 'border-transparent text-blue-100 hover:text-white hover:border-blue-300' }} inline-flex items-center px-1 pt-1 text-sm font-medium transition-all duration-200 hover:scale-105">
                            <span class="flex items-center space-x-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <span>Audit Logs</span>
                            </span>
                        </a>
                        <a href="/admin/sso-settings"
                           class="border-b-2 {{ request()->is('admin/sso-settings*') ? 'border-blue-300 text-white' : 'border-transparent text-blue-100 hover:text-white hover:border-blue-300' }} inline-flex items-center px-1 pt-1 text-sm font-medium transition-all duration-200 hover:scale-105">
                            <span class="flex items-center space-x-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                <span>SSO Settings</span>
                            </span>
                        </a>
                    </div>
                </div>
                <div class="hidden sm:ml-6 sm:flex sm:items-center space-x-4">
                    <!-- Profile Dropdown -->
                    <div class="ml-3 relative" x-data="{ open: false }">
                        <div>
                            <button @click="open = !open" class="flex items-center text-sm text-white hover:text-blue-200 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-blue-800 focus:ring-white rounded-md px-3 py-2">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                <span class="mr-1">Admin</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                        </div>

                        <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95" class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50">
                            <div class="py-1">
                                <a href="/admin/profile" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                    My Profile
                                </a>
                                <a href="/admin/security-settings" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                    </svg>
                                    Security Settings
                                </a>
                                <div class="border-t border-gray-100"></div>
                                <form method="POST" action="/auth/logout" class="block">
                                    @csrf
                                    <button type="submit" class="flex items-center w-full px-4 py-2 text-sm text-red-700 hover:bg-red-50 transition-colors">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                        </svg>
                                        Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div id="page-loading" class="hidden">
        <x-page-loading title="Loading Page">Preparing your admin dashboard...</x-page-loading>
    </div>

    <main class="flex-1 relative">
        @yield('content')
    </main>

    <footer class="bg-white border-t mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex justify-between items-center text-sm text-gray-500">
                <div>
                    CAS Admin Panel - Centralized Authentication System
                </div>
                <div class="flex items-center space-x-4">
                    <span>Laravel {{ app()->version() }}</span>
                    <span>•</span>
                    <span>Livewire Enabled</span>
                </div>
            </div>
        </div>
    </footer>


    @livewireScripts

    <script>
        function showPageLoading() {
            const loading = document.getElementById('page-loading');
            if (loading) {
                loading.classList.remove('hidden');
            }
        }

        function hidePageLoading() {
            const loading = document.getElementById('page-loading');
            if (loading) {
                loading.classList.add('hidden');
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const navLinks = document.querySelectorAll('nav a[href^="/admin"]');
            navLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    showPageLoading();

                    setTimeout(() => {
                        hidePageLoading();
                    }, 5000);
                });
            });

            window.addEventListener('livewire:navigating', () => {
                showPageLoading();
            });

            window.addEventListener('livewire:navigated', () => {
                hidePageLoading();
            });

            window.addEventListener('load', () => {
                hidePageLoading();
            });

            window.addEventListener('error', function(e) {
                if (e.filename && (e.filename.includes('share-modal.js') ||
                    e.filename.includes('extension'))) {
                    e.preventDefault();
                    return false;
                }
            });
        });
    </script>
</body>
</html>
