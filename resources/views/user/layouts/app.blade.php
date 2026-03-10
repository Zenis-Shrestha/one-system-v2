<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'User Dashboard - CAS')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>*, *::before, *::after { font-family: 'Inter', system-ui, -apple-system, sans-serif; }</style>
    @livewireStyles
</head>
<body class="bg-gray-50 min-h-screen">
    <nav class="bg-slate-800 shadow-lg border-b border-slate-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-emerald-500/20 rounded-lg flex items-center justify-center border border-emerald-400/30">
                                <svg class="w-5 h-5 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                            <h1 class="text-xl font-bold text-white">User Dashboard</h1>
                        </div>
                    </div>
                    <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                        <a href="/user/dashboard"
                           class="border-b-2 {{ request()->is('user/dashboard*') ? 'border-emerald-400 text-white' : 'border-transparent text-slate-300 hover:text-white hover:border-emerald-400' }} inline-flex items-center px-1 pt-1 text-sm font-medium transition-colors duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                            </svg>
                            SSO Dashboard
                        </a>
                        <a href="/user/profile"
                           class="border-b-2 {{ request()->is('user/profile*') ? 'border-emerald-400 text-white' : 'border-transparent text-slate-300 hover:text-white hover:border-emerald-400' }} inline-flex items-center px-1 pt-1 text-sm font-medium transition-colors duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            Profile & Security
                        </a>
                    </div>
                </div>
                <div class="hidden sm:ml-6 sm:flex sm:items-center">
                    @if(session('user_id'))
                        @php
                            $user = \App\Models\User::find(session('user_id'));
                        @endphp
                        @if($user)
                            <div class="flex items-center space-x-4">
                                <div class="text-right">
                                    <p class="text-sm font-medium text-white">{{ $user->first_name }} {{ $user->last_name }}</p>
                                    <p class="text-xs text-slate-400">{{ $user->email }}</p>
                                </div>
                                <div class="w-8 h-8 bg-emerald-500/30 rounded-lg flex items-center justify-center text-white font-semibold text-sm border border-emerald-400/40">
                                    {{ strtoupper(substr($user->first_name, 0, 1)) }}{{ strtoupper(substr($user->last_name, 0, 1)) }}
                                </div>
                                <form method="POST" action="{{ route('logout') }}" class="inline">
                                    @csrf
                                    <button type="submit" class="bg-slate-700 hover:bg-slate-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200 border border-slate-600 hover:border-slate-500 flex items-center space-x-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                        </svg>
                                        <span>Logout</span>
                                    </button>
                                </form>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </nav>

    <main class="flex-1">
        @yield('content')
    </main>

    <footer class="bg-white border-t mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex justify-center items-center text-sm text-gray-500">
                <div>
                    Central Authentication Service - User Dashboard
                </div>
            </div>
        </div>
    </footer>


    @livewireScripts
    @stack('scripts')
</body>
</html>
