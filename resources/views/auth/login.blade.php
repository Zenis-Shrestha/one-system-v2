<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - CAS Authentication</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    {!! RecaptchaV3::initJs() !!}
    <style>
        *, *::before, *::after { font-family: 'Inter', system-ui, -apple-system, sans-serif; }
        [x-cloak] { display: none !important; }

        body {
            background: #0f172a;
            overflow: hidden;
        }

        .login-grid {
            position: fixed;
            inset: 0;
            background-image:
                linear-gradient(rgba(59, 130, 246, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(59, 130, 246, 0.03) 1px, transparent 1px);
            background-size: 60px 60px;
            z-index: 0;
        }

        .login-glow {
            position: fixed;
            width: 500px;
            height: 500px;
            border-radius: 50%;
            filter: blur(120px);
            opacity: 0.15;
            z-index: 0;
            animation: float 8s ease-in-out infinite;
        }

        .glow-1 {
            background: #3b82f6;
            top: -100px;
            right: -100px;
        }

        .glow-2 {
            background: #6366f1;
            bottom: -100px;
            left: -100px;
            animation-delay: -4s;
        }

        @keyframes float {
            0%, 100% { transform: translate(0, 0); }
            50% { transform: translate(-20px, 20px); }
        }

        .login-card {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(148, 163, 184, 0.1);
            border-radius: 16px;
            position: relative;
            z-index: 10;
        }

        .login-card::before {
            content: '';
            position: absolute;
            inset: -1px;
            border-radius: 17px;
            padding: 1px;
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.3), transparent 50%, rgba(99, 102, 241, 0.2));
            -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
            pointer-events: none;
        }

        .form-input {
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(148, 163, 184, 0.15);
            border-radius: 10px;
            padding: 12px 16px;
            color: #e2e8f0;
            font-size: 14px;
            width: 100%;
            transition: all 0.2s ease;
            outline: none;
        }

        .form-input::placeholder {
            color: #64748b;
        }

        .form-input:focus {
            border-color: rgba(59, 130, 246, 0.5);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .btn-login {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            border: none;
            border-radius: 10px;
            padding: 12px;
            color: white;
            font-weight: 600;
            font-size: 14px;
            width: 100%;
            cursor: pointer;
            transition: all 0.2s ease;
            position: relative;
            overflow: hidden;
        }

        .btn-login:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 25px -5px rgba(59, 130, 246, 0.4);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .shield-icon {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, #3b82f6, #6366f1);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
            box-shadow: 0 8px 25px -5px rgba(59, 130, 246, 0.3);
        }

        .feature-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 12px;
            background: rgba(59, 130, 246, 0.1);
            border: 1px solid rgba(59, 130, 246, 0.15);
            border-radius: 20px;
            font-size: 11px;
            color: #93c5fd;
            font-weight: 500;
        }

        .alert-box {
            border-radius: 10px;
            padding: 12px 16px;
            font-size: 13px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: #fca5a5;
        }

        .alert-success {
            background: rgba(34, 197, 94, 0.1);
            border: 1px solid rgba(34, 197, 94, 0.2);
            color: #86efac;
        }

        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 500;
            color: #94a3b8;
            margin-bottom: 6px;
        }

        .info-panel {
            position: relative;
            z-index: 10;
        }

        @media (max-width: 1024px) {
            .info-panel { display: none; }
        }

        .stat-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 0;
            border-bottom: 1px solid rgba(148, 163, 184, 0.08);
        }

        .stat-item:last-child {
            border-bottom: none;
        }

        .stat-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .fade-in {
            animation: fadeIn 0.5s ease forwards;
            opacity: 0;
        }

        @keyframes fadeIn {
            to { opacity: 1; }
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <div class="login-grid"></div>
    <div class="login-glow glow-1"></div>
    <div class="login-glow glow-2"></div>

    <div class="flex items-center gap-16 max-w-5xl w-full fade-in">
        <div class="info-panel flex-1">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-white mb-3">CAS Authentication</h1>
                <p class="text-slate-400 text-base leading-relaxed">
                    Central Authentication Service for secure single sign-on across all your connected applications.
                </p>
            </div>

            <div class="flex gap-3 mb-10">
                <span class="feature-badge">
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    SSO Enabled
                </span>
                <span class="feature-badge">
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path>
                    </svg>
                    2FA Protected
                </span>
                <span class="feature-badge">
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.056-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    Encrypted
                </span>
            </div>

            <div class="mb-8">
                <div class="stat-item">
                    <div class="stat-icon" style="background: rgba(59, 130, 246, 0.15);">
                        <svg class="w-4.5 h-4.5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                    </div>
                    <div>
                        <div class="text-sm font-medium text-slate-200">Enterprise Security</div>
                        <div class="text-xs text-slate-500">JWT tokens with configurable expiry</div>
                    </div>
                </div>
                <div class="stat-item">
                    <div class="stat-icon" style="background: rgba(34, 197, 94, 0.15);">
                        <svg class="w-4.5 h-4.5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <div>
                        <div class="text-sm font-medium text-slate-200">Instant Access</div>
                        <div class="text-xs text-slate-500">One login for all connected systems</div>
                    </div>
                </div>
                <div class="stat-item">
                    <div class="stat-icon" style="background: rgba(168, 85, 247, 0.15);">
                        <svg class="w-4.5 h-4.5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <div>
                        <div class="text-sm font-medium text-slate-200">Full Audit Trail</div>
                        <div class="text-xs text-slate-500">Track every authentication event</div>
                    </div>
                </div>
            </div>

            <p class="text-xs text-slate-600">
                © {{ date('Y') }} CAS Authentication System &middot; v1.0
            </p>
        </div>

        <div class="login-card w-full max-w-md p-8">
            <div class="text-center mb-8">
                <div class="shield-icon">
                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                </div>
                <h2 class="mt-5 text-xl font-semibold text-white">Sign in to CAS</h2>
                <p class="mt-1 text-sm text-slate-400">Enter your credentials to continue</p>
            </div>

            <form action="{{ route('login.post') }}" method="POST" id="login-form">
                @csrf
                {!! RecaptchaV3::field('login') !!}

                <div class="space-y-5">
                    <div>
                        <label for="login" class="form-label">Username or Email</label>
                        <input id="login" name="login" type="text" required
                               class="form-input"
                               placeholder="Enter your username or email"
                               value="{{ old('login') }}">
                    </div>

                    <div x-data="{ show: false }" class="relative">
                        <label for="password" class="form-label">Password</label>
                        <div class="relative">
                            <input id="password" name="password" :type="show ? 'text' : 'password'" required
                                   class="form-input pr-11"
                                   placeholder="Enter your password">
                            <button type="button" @click="show = !show"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-slate-300 transition-colors focus:outline-none"
                                    :title="show ? 'Hide password' : 'Show password'">
                                <svg x-show="!show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                <svg x-show="show" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input id="remember" name="remember" type="checkbox"
                                   class="h-4 w-4 rounded border-slate-600 bg-slate-800 text-blue-600 focus:ring-blue-500 focus:ring-offset-0">
                            <label for="remember" class="ml-2 text-sm text-slate-400">
                                Remember me
                            </label>
                        </div>
                        <a href="{{ route('password.request') }}" class="text-sm text-blue-400 hover:text-blue-300 transition-colors">
                            Forgot password?
                        </a>
                    </div>
                </div>

                @if(session('error'))
                    <div class="alert-box alert-error mt-5">
                        <svg class="h-4 w-4 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                        </svg>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                @if(session('message'))
                    <div class="alert-box alert-success mt-5">
                        <svg class="h-4 w-4 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        <span>{{ session('message') }}</span>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert-box alert-error mt-5">
                        <svg class="h-4 w-4 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                        </svg>
                        <div>
                            @foreach($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </div>
                    </div>
                @endif

                <button type="submit" class="btn-login mt-6">
                    Sign in
                </button>

                <p class="mt-4 text-xs text-slate-500 text-center">
                    Protected by reCAPTCHA &middot;
                    <a href="https://policies.google.com/privacy" class="text-slate-400 hover:text-slate-300">Privacy</a> &middot;
                    <a href="https://policies.google.com/terms" class="text-slate-400 hover:text-slate-300">Terms</a>
                </p>
            </form>

            <div class="mt-6 pt-6 border-t border-slate-700/50 text-center">
                <a href="/docs" class="text-sm text-slate-400 hover:text-blue-400 transition-colors inline-flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                    View API Documentation
                </a>
            </div>
        </div>
    </div>
</body>
</html>
