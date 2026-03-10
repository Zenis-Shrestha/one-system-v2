<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'CAS SSO Documentation')</title>
    <meta name="description" content="@yield('description', 'Complete integration guide for CAS Single Sign-On authentication system')">

    <script src="https://cdn.tailwindcss.com"></script>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism-tomorrow.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/prism.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-php.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-csharp.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-javascript.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-java.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-python.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-bash.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-json.min.js"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        .code-block {
            background: #1e1e1e;
            border-radius: 8px;
            overflow-x: auto;
        }

        .sidebar-nav a.active {
            background: #4f46e5;
            color: white;
        }

        .nav-bg {
            background: #4f46e5;
        }

        .hero-pattern {
            background: transparent;
        }

        .nav-item:hover {
            transform: translateX(4px);
            transition: all 0.3s ease;
        }

        .card-hover:hover {
            transform: translateY(-5px);
            transition: all 0.3s ease;
        }

        .scroll-smooth {
            scroll-behavior: smooth;
        }

        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
    </style>
</head>
<body class="bg-gray-50 font-sans scroll-smooth">
    <nav class="nav-bg text-white shadow-xl sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center">
                    <a href="{{ route('docs.index') }}" class="flex items-center space-x-3">
                        <i class="fas fa-shield-alt text-2xl"></i>
                        <span class="text-xl font-bold">CAS SSO Docs</span>
                    </a>
                </div>

                <div class="hidden md:flex items-center space-x-8">
                    <a href="{{ route('docs.index') }}" class="hover:text-blue-200 transition-colors">
                        <i class="fas fa-home mr-2"></i>Home
                    </a>
                    <a href="{{ route('docs.api.overview') }}" class="hover:text-blue-200 transition-colors">
                        <i class="fas fa-code mr-2"></i>API Reference
                    </a>
                    <a href="{{ route('docs.examples') }}" class="hover:text-blue-200 transition-colors">
                        <i class="fas fa-play mr-2"></i>Examples
                    </a>
                    <a href="/" class="bg-white text-blue-600 px-4 py-2 rounded-full hover:bg-blue-50 transition-colors">
                        <i class="fas fa-arrow-left mr-2"></i>Back to CAS
                    </a>
                </div>

                <div class="md:hidden">
                    <button id="mobile-menu-btn" class="text-white hover:text-blue-200">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>
            </div>
        </div>

        <div id="mobile-menu" class="hidden md:hidden bg-blue-800 bg-opacity-90">
            <div class="px-2 pt-2 pb-3 space-y-1">
                <a href="{{ route('docs.index') }}" class="block px-3 py-2 text-white hover:bg-blue-700 rounded">
                    <i class="fas fa-home mr-2"></i>Home
                </a>
                <a href="{{ route('docs.api.overview') }}" class="block px-3 py-2 text-white hover:bg-blue-700 rounded">
                    <i class="fas fa-code mr-2"></i>API Reference
                </a>
                <a href="{{ route('docs.examples') }}" class="block px-3 py-2 text-white hover:bg-blue-700 rounded">
                    <i class="fas fa-play mr-2"></i>Examples
                </a>
                <a href="/" class="block px-3 py-2 text-white hover:bg-blue-700 rounded">
                    <i class="fas fa-arrow-left mr-2"></i>Back to CAS
                </a>
            </div>
        </div>
    </nav>

    <div class="flex">
        @if(Request::is('docs/*') && !Request::is('docs'))
        <aside class="w-64 bg-white shadow-lg min-h-screen sticky top-16 overflow-y-auto">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">
                    <i class="fas fa-book mr-2"></i>Documentation
                </h3>
                <nav class="space-y-2 sidebar-nav">
                    <a href="{{ route('docs.laravel') }}" class="nav-item block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-lg {{ Request::is('docs/laravel') ? 'active' : '' }}">
                        <i class="fab fa-laravel mr-2"></i>Laravel
                    </a>
                    <a href="{{ route('docs.dotnet') }}" class="nav-item block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-lg {{ Request::is('docs/dotnet') ? 'active' : '' }}">
                        <i class="fab fa-microsoft mr-2"></i>.NET MVC C#
                    </a>
                    <a href="{{ route('docs.nodejs') }}" class="nav-item block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-lg {{ Request::is('docs/nodejs') ? 'active' : '' }}">
                        <i class="fab fa-node-js mr-2"></i>Node.js
                    </a>
                    <a href="{{ route('docs.java') }}" class="nav-item block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-lg {{ Request::is('docs/java') ? 'active' : '' }}">
                        <i class="fab fa-java mr-2"></i>Java Spring
                    </a>
                    <a href="{{ route('docs.python') }}" class="nav-item block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-lg {{ Request::is('docs/python') ? 'active' : '' }}">
                        <i class="fab fa-python mr-2"></i>Python Django
                    </a>
                    <a href="{{ route('docs.javascript') }}" class="nav-item block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-lg {{ Request::is('docs/javascript') ? 'active' : '' }}">
                        <i class="fab fa-js mr-2"></i>JavaScript/HTML
                    </a>
                </nav>
            </div>
        </aside>
        @endif

        <main class="flex-1 @if(Request::is('docs/*') && !Request::is('docs')) ml-0 @endif">
            @yield('content')
        </main>
    </div>

    <footer class="bg-gray-800 text-white py-12 mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <h4 class="text-lg font-semibold mb-4">
                        <i class="fas fa-shield-alt mr-2"></i>CAS SSO
                    </h4>
                    <p class="text-gray-400">
                        Enterprise-grade Single Sign-On authentication system for secure multi-platform integration.
                    </p>
                </div>

                <div>
                    <h4 class="text-lg font-semibold mb-4">Documentation</h4>
                    <ul class="text-gray-400 space-y-2">
                        <li><a href="{{ route('docs.laravel') }}" class="hover:text-white">Laravel</a></li>
                        <li><a href="{{ route('docs.dotnet') }}" class="hover:text-white">.NET MVC</a></li>
                        <li><a href="{{ route('docs.nodejs') }}" class="hover:text-white">Node.js</a></li>
                        <li><a href="{{ route('docs.java') }}" class="hover:text-white">Java Spring</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="text-lg font-semibold mb-4">Resources</h4>
                    <ul class="text-gray-400 space-y-2">
                        <li><a href="{{ route('docs.api.overview') }}" class="hover:text-white">API Reference</a></li>
                        <li><a href="{{ route('docs.examples') }}" class="hover:text-white">Examples</a></li>
                        <li><a href="/" class="hover:text-white">CAS Dashboard</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="text-lg font-semibold mb-4">Support</h4>
                    <ul class="text-gray-400 space-y-2">
                        <li><a href="#" class="hover:text-white">Contact Support</a></li>
                        <li><a href="#" class="hover:text-white">GitHub</a></li>
                        <li><a href="#" class="hover:text-white">Community</a></li>
                    </ul>
                </div>
            </div>

            <div class="mt-8 pt-8 border-t border-gray-700 text-center text-gray-400">
                <p>&copy; 2025 CAS SSO System. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        document.getElementById('mobile-menu-btn').addEventListener('click', function() {
            const menu = document.getElementById('mobile-menu');
            menu.classList.toggle('hidden');
        });

        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            const codeBlocks = document.querySelectorAll('pre[class*="language-"]');
            codeBlocks.forEach(block => {
                const copyBtn = document.createElement('button');
                copyBtn.innerHTML = '<i class="fas fa-copy"></i>';
                copyBtn.className = 'absolute top-2 right-2 bg-gray-700 text-white px-2 py-1 rounded text-xs hover:bg-gray-600';
                block.style.position = 'relative';
                block.appendChild(copyBtn);

                copyBtn.addEventListener('click', function() {
                    const code = block.querySelector('code');
                    navigator.clipboard.writeText(code.textContent);
                    copyBtn.innerHTML = '<i class="fas fa-check"></i>';
                    setTimeout(() => {
                        copyBtn.innerHTML = '<i class="fas fa-copy"></i>';
                    }, 2000);
                });
            });
        });
    </script>
</body>
</html>
