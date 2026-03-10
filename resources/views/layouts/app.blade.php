<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel CAS Server') }} - @yield('title', 'Central Authentication Service')</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('head')

    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }

        .hero-section {
            background: #1e293b;
            color: white;
            padding: 80px 0;
            position: relative;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.2);
            z-index: 1;
        }

        .hero-section::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 2;
        }

        .hero-content {
            position: relative;
            z-index: 10;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            text-align: center;
        }

        .hero-title {
            font-size: 3.5rem;
            font-weight: bold;
            margin-bottom: 1.5rem;
            color: white;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        .hero-subtitle {
            font-size: 1.25rem;
            margin-bottom: 3rem;
            color: #e2e8f0;
            line-height: 1.6;
        }

        /* Access Cards */
        .access-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 4rem;
        }

        .access-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 1rem;
            padding: 2rem;
            text-align: center;
            transition: all 0.3s ease;
        }

        .access-card:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-4px);
        }

        .card-icon {
            width: 64px;
            height: 64px;
            margin: 0 auto 1.5rem;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }

        .card-icon.blue { background: #2563eb; }
        .card-icon.green { background: #16a34a; }
        .card-icon.purple { background: #9333ea; }

        .card-title {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 1rem;
            color: white;
        }

        .card-description {
            margin-bottom: 2rem;
            color: #cbd5e1;
            line-height: 1.5;
        }

        .card-button {
            display: inline-block;
            padding: 12px 32px;
            border-radius: 12px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            transform: translateY(0);
        }

        .card-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }

        .btn-white {
            background: white;
            color: #1e293b;
        }

        .btn-green {
            background: #16a34a;
            color: white;
        }

        .btn-purple {
            background: #9333ea;
            color: white;
        }

        /* Features Section */
        .features-section {
            background: white;
            padding: 80px 0;
        }

        .features-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .features-header {
            text-align: center;
            margin-bottom: 4rem;
        }

        .features-title {
            font-size: 2.5rem;
            font-weight: bold;
            color: #1e293b;
            margin-bottom: 1.5rem;
        }

        .features-subtitle {
            font-size: 1.25rem;
            color: #64748b;
            max-width: 800px;
            margin: 0 auto;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
        }

        .feature-card {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 1rem;
            padding: 2rem;
            text-align: center;
            transition: all 0.3s ease;
        }

        .feature-card:hover {
            border-color: #3b82f6;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }

        .feature-icon {
            width: 64px;
            height: 64px;
            margin: 0 auto 1.5rem;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }

        .feature-icon.blue { background: #2563eb; }
        .feature-icon.green { background: #16a34a; }
        .feature-icon.purple { background: #9333ea; }
        .feature-icon.orange { background: #ea580c; }

        .feature-title {
            font-size: 1.25rem;
            font-weight: bold;
            color: #1e293b;
            margin-bottom: 0.75rem;
        }

        .feature-description {
            color: #64748b;
            line-height: 1.6;
        }

        /* CTA Section */
        .cta-section {
            background: #f8fafc;
            padding: 80px 0;
        }

        .cta-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            text-align: center;
        }

        .cta-title {
            font-size: 2.5rem;
            font-weight: bold;
            color: #1e293b;
            margin-bottom: 1.5rem;
        }

        .cta-subtitle {
            font-size: 1.25rem;
            color: #64748b;
            margin-bottom: 3rem;
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
        }

        .cta-buttons {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 1.5rem;
        }

        .cta-button {
            display: inline-block;
            padding: 16px 32px;
            border-radius: 12px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 1rem;
        }

        .cta-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }

        .btn-primary {
            background: #2563eb;
            color: white;
        }

        .btn-secondary {
            background: white;
            color: #475569;
            border: 2px solid #cbd5e1;
        }

        .btn-secondary:hover {
            border-color: #94a3b8;
            color: #1e293b;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem;
            }

            .hero-subtitle {
                font-size: 1.125rem;
            }

            .access-cards {
                grid-template-columns: 1fr;
            }

            .features-grid {
                grid-template-columns: 1fr;
            }

            .cta-buttons {
                flex-direction: column;
                align-items: center;
            }
        }
    </style>
</head>
<body class="font-sans antialiased">
    <div id="app">
        @hasSection('navigation')
            @yield('navigation')
        @else
            <nav class="bg-white shadow-lg">
                <div class="max-w-7xl mx-auto px-4">
                    <div class="flex justify-between h-16">
                        <div class="flex items-center">
                            <a href="{{ url('/') }}" class="flex items-center">
                                <span class="text-xl font-bold text-gray-800">Laravel CAS Server</span>
                            </a>
                        </div>
                        <div class="flex items-center space-x-4">
                            <a href="{{ route('user.dashboard') }}" class="text-gray-600 hover:text-gray-900">User Dashboard</a>
                            <a href="{{ route('docs') }}" class="text-gray-600 hover:text-gray-900">Documentation</a>
                        </div>
                    </div>
                </div>
            </nav>
        @endif

        <main>
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 mx-4 mt-4">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 mx-4 mt-4">
                    {{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 mx-4 mt-4">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>

        <!-- Footer -->
        @hasSection('footer')
            @yield('footer')
        @else
            <footer class="bg-gray-800 text-white py-8 mt-12">
                <div class="max-w-6xl mx-auto px-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        <div>
                            <h3 class="text-lg font-semibold mb-4">Laravel CAS Server</h3>
                            <p class="text-gray-300">Enterprise-grade Central Authentication Service for secure single sign-on across multiple applications.</p>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold mb-4">Quick Links</h3>
                            <ul class="space-y-2">
                                <li><a href="{{ route('user.dashboard') }}" class="text-gray-300 hover:text-white">User Dashboard</a></li>
                                <li><a href="{{ route('docs') }}" class="text-gray-300 hover:text-white">Documentation</a></li>
                                <li><a href="/health" class="text-gray-300 hover:text-white">System Health</a></li>
                            </ul>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold mb-4">Features</h3>
                            <ul class="space-y-2 text-gray-300">
                                <li>• Secure Authentication</li>
                                <li>• SSO Integration</li>
                                <li>• Audit & Monitoring</li>
                                <li>• Docker Ready</li>
                            </ul>
                        </div>
                    </div>
                    <div class="border-t border-gray-700 pt-8 mt-8 text-center">
                        <p class="text-gray-400">&copy; {{ date('Y') }} Laravel CAS Server. Enterprise authentication solution.</p>
                    </div>
                </div>
            </footer>
        @endif
    </div>

    @stack('scripts')

    @livewireScripts
</body>
</html>
