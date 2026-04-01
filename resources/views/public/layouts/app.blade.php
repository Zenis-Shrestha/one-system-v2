<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'CAS Documentation')</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-core.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/plugins/autoloader/prism-autoloader.min.js"></script>
</head>
<body class="bg-white min-h-screen">
    <nav class="bg-gray-800 shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center">
                        <h1 class="text-xl font-bold text-white">CAS Documentation</h1>
                    </div>
                    <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                        <a href="/docs"
                           class="border-b-2 {{ request()->is('docs') || request()->is('/') ? 'border-gray-300 text-white' : 'border-transparent text-gray-300 hover:text-white hover:border-gray-300' }} inline-flex items-center px-1 pt-1 text-sm font-medium">
                            Overview
                        </a>
                        <a href="/docs/authentication"
                           class="border-b-2 {{ request()->is('docs/authentication*') ? 'border-gray-300 text-white' : 'border-transparent text-gray-300 hover:text-white hover:border-gray-300' }} inline-flex items-center px-1 pt-1 text-sm font-medium">
                            Authentication
                        </a>
                        <a href="/docs/api"
                           class="border-b-2 {{ request()->is('docs/api*') ? 'border-gray-300 text-white' : 'border-transparent text-gray-300 hover:text-white hover:border-gray-300' }} inline-flex items-center px-1 pt-1 text-sm font-medium">
                            API Reference
                        </a>
                        <a href="/docs/examples"
                           class="border-b-2 {{ request()->is('docs/examples*') ? 'border-gray-300 text-white' : 'border-transparent text-gray-300 hover:text-white hover:border-gray-300' }} inline-flex items-center px-1 pt-1 text-sm font-medium">
                            Examples
                        </a>
                    </div>
                </div>
                <div class="hidden sm:ml-6 sm:flex sm:items-center">
                    <a href="https://github.com/your-repo/insol-dev" target="_blank" class="text-gray-300 hover:text-white">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 0C4.477 0 0 4.484 0 10.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0110 4.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.203 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.942.359.31.678.921.678 1.856 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0020 10.017C20 4.484 15.522 0 10 0z" clip-rule="evenodd"></path>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <main class="flex-1">
        @yield('content')
    </main>

    <footer class="bg-gray-100 border-t mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex justify-between items-center text-sm text-gray-600">
                <div>
                    <p>Central Authentication Service (CAS) Documentation</p>
                    <p class="text-xs mt-1">Open source authentication solution for enterprise applications</p>
                </div>
                <div class="flex items-center space-x-4">
                    <span>Version 2.0</span>
                    <span>•</span>
                    <span>Laravel {{ app()->version() }}</span>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>
