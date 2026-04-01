@extends('public.documentation.layout')

@section('title', 'User Guide — CAS SSO')
@section('description', 'Learn how to use the CAS Single Sign-On system as an end user — login, profile, connected apps, and security settings.')

@section('content')
<div class="max-w-4xl">
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center space-x-2 text-sm text-gray-500 mb-4">
            <a href="{{ route('docs') }}" class="hover:text-blue-600">Docs</a>
            <i class="fas fa-chevron-right text-xs"></i>
            <span>User Guide</span>
        </div>
        <h1 class="text-3xl font-bold text-gray-900 mb-2">User Guide</h1>
        <p class="text-lg text-gray-600">A comprehensive guide for end users of the CAS Single Sign-On system.</p>
    </div>

    <!-- Getting Started -->
    <div class="bg-white border border-gray-200 rounded-lg p-6 mb-8">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">
            <i class="fas fa-play-circle text-blue-600 mr-2"></i>Getting Started
        </h2>
        <p class="text-gray-700 mb-4">
            The CAS (Central Authentication Service) provides a single set of credentials to access all connected applications. Once your account is created by an administrator, you can log in and manage your profile.
        </p>

        <h3 class="text-lg font-medium text-gray-900 mb-3">Logging In</h3>
        <ol class="list-decimal list-inside space-y-2 text-gray-700 mb-4">
            <li>Navigate to the CAS login page at <code class="bg-gray-100 px-2 py-1 rounded text-sm">/auth/login</code></li>
            <li>Enter your <strong>email address</strong> and <strong>password</strong></li>
            <li>If Two-Factor Authentication (2FA) is enabled, enter the OTP code from your authenticator app</li>
            <li>You will be redirected to your <strong>User Dashboard</strong></li>
        </ol>

        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <p class="text-blue-800 text-sm"><i class="fas fa-info-circle mr-1"></i> <strong>Tip:</strong> If you forget your password, contact your system administrator to have it reset.</p>
        </div>
    </div>

    <!-- User Dashboard -->
    <div class="bg-white border border-gray-200 rounded-lg p-6 mb-8">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">
            <i class="fas fa-tachometer-alt text-green-600 mr-2"></i>User Dashboard
        </h2>
        <p class="text-gray-700 mb-4">
            After logging in, the User Dashboard provides an overview of your connected applications and account status.
        </p>

        <h3 class="text-lg font-medium text-gray-900 mb-3">Dashboard Features</h3>
        <ul class="space-y-3 text-gray-700">
            <li class="flex items-start">
                <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
                <div>
                    <strong>Connected Applications</strong> — View all client systems you have access to, along with their SSO status.
                </div>
            </li>
            <li class="flex items-start">
                <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
                <div>
                    <strong>Launch Applications</strong> — Click "Launch Application" on any connected system to SSO directly into it without re-entering credentials.
                </div>
            </li>
            <li class="flex items-start">
                <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
                <div>
                    <strong>Quick Actions</strong> — Access profile settings and refresh your dashboard data.
                </div>
            </li>
        </ul>
    </div>

    <!-- Profile & Security -->
    <div class="bg-white border border-gray-200 rounded-lg p-6 mb-8">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">
            <i class="fas fa-user-cog text-purple-600 mr-2"></i>Profile & Security
        </h2>
        <p class="text-gray-700 mb-4">
            Manage your personal information and security settings from the Profile page.
        </p>

        <h3 class="text-lg font-medium text-gray-900 mb-3">Personal Information</h3>
        <p class="text-gray-700 mb-4">Update your name, contact details, and other account information. Changes are saved immediately and reflected across all connected applications.</p>

        <h3 class="text-lg font-medium text-gray-900 mb-3">Changing Your Password</h3>
        <ol class="list-decimal list-inside space-y-2 text-gray-700 mb-4">
            <li>Go to <strong>Profile & Security</strong> from the navigation bar</li>
            <li>Click the <strong>Security</strong> tab</li>
            <li>Enter your <strong>current password</strong></li>
            <li>Enter and confirm your <strong>new password</strong></li>
            <li>Click <strong>Update Password</strong></li>
        </ol>

        <h3 class="text-lg font-medium text-gray-900 mb-3">Two-Factor Authentication (2FA)</h3>
        <p class="text-gray-700 mb-4">If 2FA is enabled by your administrator, you will need an authenticator app (Google Authenticator, Authy, etc.) to generate time-based one-time passwords (TOTP) during login.</p>

        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <p class="text-yellow-800 text-sm"><i class="fas fa-exclamation-triangle mr-1"></i> <strong>Important:</strong> Keep your 2FA recovery codes in a secure location. If you lose access to your authenticator app, you will need to contact your administrator.</p>
        </div>
    </div>

    <!-- SSO Login Flow -->
    <div class="bg-white border border-gray-200 rounded-lg p-6 mb-8">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">
            <i class="fas fa-exchange-alt text-indigo-600 mr-2"></i>How SSO Login Works
        </h2>
        <p class="text-gray-700 mb-4">
            Single Sign-On allows you to access multiple applications with one login:
        </p>
        <ol class="list-decimal list-inside space-y-3 text-gray-700">
            <li><strong>Login once</strong> — Sign in to CAS with your credentials</li>
            <li><strong>Click "Launch Application"</strong> — From your dashboard, select the app you want to use</li>
            <li><strong>Automatic authentication</strong> — CAS sends a secure token to the target application, and you are logged in automatically</li>
            <li><strong>Seamless access</strong> — No need to enter credentials again for any connected app during your session</li>
        </ol>
    </div>

    <!-- Troubleshooting for Users -->
    <div class="bg-white border border-gray-200 rounded-lg p-6 mb-8">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">
            <i class="fas fa-question-circle text-orange-600 mr-2"></i>Common Issues
        </h2>

        <div class="space-y-4">
            <div class="border-l-4 border-blue-400 pl-4">
                <h4 class="font-medium text-gray-900">Can't log in?</h4>
                <p class="text-gray-600 text-sm">Check your email and password. If you've forgotten your password, contact your system administrator to reset it.</p>
            </div>
            <div class="border-l-4 border-blue-400 pl-4">
                <h4 class="font-medium text-gray-900">2FA code not working?</h4>
                <p class="text-gray-600 text-sm">Make sure the time on your phone is synced correctly. TOTP codes are time-sensitive and expire every 30 seconds.</p>
            </div>
            <div class="border-l-4 border-blue-400 pl-4">
                <h4 class="font-medium text-gray-900">Application not launching?</h4>
                <p class="text-gray-600 text-sm">The client system may be offline or your access may not be configured. Contact your administrator.</p>
            </div>
            <div class="border-l-4 border-blue-400 pl-4">
                <h4 class="font-medium text-gray-900">Need help?</h4>
                <p class="text-gray-600 text-sm">Reach out to your system administrator or contact the CAS development team at <a href="https://innovativesolution.com.np/" target="_blank" class="font-semibold text-blue-600 hover:text-blue-800">innovativesolution.com.np</a>.</p>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <div class="flex justify-between items-center border-t border-gray-200 pt-6">
        <a href="{{ route('docs.admin-panel') }}" class="flex items-center text-blue-600 hover:text-blue-800">
            <i class="fas fa-arrow-left mr-2"></i>Admin Guide
        </a>
        <a href="{{ route('docs.security-guide') }}" class="flex items-center text-blue-600 hover:text-blue-800">
            Security Guide<i class="fas fa-arrow-right ml-2"></i>
        </a>
    </div>
</div>
@endsection
