@extends('public.documentation.layout')

@section('title', 'Security Guide — CAS SSO')
@section('description', 'Security hardening best practices for CAS SSO at both application and server levels.')

@section('content')
<div class="max-w-4xl">
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center space-x-2 text-sm text-gray-500 mb-4">
            <a href="{{ route('docs') }}" class="hover:text-blue-600">Docs</a>
            <i class="fas fa-chevron-right text-xs"></i>
            <span>Security Guide</span>
        </div>
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Security Guide</h1>
        <p class="text-lg text-gray-600">Must-have and recommended security features for hardening your CAS deployment at the application and server level.</p>
    </div>

    <!-- Application-Level Security -->
    <div class="bg-white border border-gray-200 rounded-lg p-6 mb-8">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">
            <i class="fas fa-shield-alt text-blue-600 mr-2"></i>Application-Level Security (Must Have)
        </h2>

        <div class="space-y-6">
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">1. HTTPS Enforcement</h3>
                <p class="text-gray-700 mb-2">All CAS traffic must use HTTPS. Configure your web server to redirect all HTTP requests to HTTPS. Never transmit authentication tokens over unencrypted connections.</p>
                <div class="code-block">
                    <pre># Nginx — force HTTPS redirect
server {
    listen 80;
    server_name cas.yourdomain.com;
    return 301 https://$host$request_uri;
}</pre>
                </div>
            </div>

            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">2. CSRF Protection</h3>
                <p class="text-gray-700 mb-2">CAS uses Laravel's built-in CSRF protection. All POST/PUT/DELETE requests require a valid CSRF token. Client applications should include the CSRF token in form submissions.</p>
            </div>

            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">3. Two-Factor Authentication (2FA)</h3>
                <p class="text-gray-700 mb-2">Enable 2FA for all admin accounts at minimum. CAS supports TOTP-based 2FA via Google Authenticator, Authy, and similar apps.</p>
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <p class="text-red-800 text-sm"><i class="fas fa-exclamation-circle mr-1"></i> <strong>Critical:</strong> Admin accounts without 2FA are a high-risk security vulnerability. Always enforce 2FA for administrators.</p>
                </div>
            </div>

            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">4. HMAC Request Signing</h3>
                <p class="text-gray-700 mb-2">All API requests between client systems and CAS are signed using HMAC-SHA256. This prevents request tampering and replay attacks. Each client system has a unique API secret used for signing.</p>
            </div>

            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">5. JWT Token Security</h3>
                <p class="text-gray-700 mb-2">SSO tokens are JWT-based with short expiry times. Best practices:</p>
                <ul class="list-disc list-inside space-y-1 text-gray-700">
                    <li>Tokens expire after a configurable time (default: 1 hour)</li>
                    <li>Tokens are signed with a secure secret key</li>
                    <li>Always validate tokens server-side, never trust client-side validation alone</li>
                    <li>Rotate JWT secrets periodically</li>
                </ul>
            </div>

            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">6. IP Whitelist</h3>
                <p class="text-gray-700 mb-2">CAS provides an IP whitelist feature to restrict access to known, trusted IP addresses. When the whitelist is empty, all IPs are allowed. Once entries are added, only whitelisted IPs can access protected routes.</p>
                <ul class="list-disc list-inside space-y-1 text-gray-700">
                    <li>Add production server IPs to the whitelist</li>
                    <li>Supports CIDR notation for IP ranges</li>
                    <li>Admin routes are always accessible (to prevent lockout)</li>
                </ul>
            </div>

            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">7. Rate Limiting</h3>
                <p class="text-gray-700 mb-2">API routes are rate-limited to prevent brute force and denial-of-service attacks. Default limits are configured in Laravel's throttle middleware. Customize limits based on your traffic requirements.</p>
            </div>
        </div>
    </div>

    <!-- Server-Level Security -->
    <div class="bg-white border border-gray-200 rounded-lg p-6 mb-8">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">
            <i class="fas fa-server text-green-600 mr-2"></i>Server-Level Security (Recommended)
        </h2>

        <div class="space-y-6">
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">1. Firewall Configuration</h3>
                <p class="text-gray-700 mb-2">Configure UFW or iptables to only allow necessary ports:</p>
                <div class="code-block">
                    <pre># Allow only SSH, HTTP, HTTPS
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable</pre>
                </div>
            </div>

            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">2. SSH Hardening</h3>
                <ul class="list-disc list-inside space-y-1 text-gray-700">
                    <li>Disable root login via SSH</li>
                    <li>Use SSH key-based authentication instead of passwords</li>
                    <li>Change the default SSH port (optional)</li>
                    <li>Use fail2ban to block brute force SSH attempts</li>
                </ul>
            </div>

            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">3. Docker Security</h3>
                <p class="text-gray-700 mb-2">If running CAS in Docker (recommended deployment):</p>
                <ul class="list-disc list-inside space-y-1 text-gray-700">
                    <li>Use non-root users inside containers</li>
                    <li>Keep Docker and images updated regularly</li>
                    <li>Limit container resource usage (CPU, memory)</li>
                    <li>Don't expose database ports to the public network</li>
                </ul>
            </div>

            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">4. Database Security</h3>
                <ul class="list-disc list-inside space-y-1 text-gray-700">
                    <li>Use strong, unique passwords for database users</li>
                    <li>Don't expose MySQL/PostgreSQL ports publicly (keep on internal Docker network)</li>
                    <li>Enable SSL for database connections in production</li>
                    <li>Perform regular database backups</li>
                </ul>
            </div>

            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">5. SSL/TLS Configuration</h3>
                <p class="text-gray-700 mb-2">Use strong TLS configuration:</p>
                <ul class="list-disc list-inside space-y-1 text-gray-700">
                    <li>Use TLS 1.2+ only (disable TLS 1.0 and 1.1)</li>
                    <li>Use Let's Encrypt or a trusted CA for certificates</li>
                    <li>Enable HSTS headers</li>
                    <li>Automate certificate renewal</li>
                </ul>
            </div>

            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">6. Regular Updates</h3>
                <p class="text-gray-700 mb-2">Keep all components updated:</p>
                <ul class="list-disc list-inside space-y-1 text-gray-700">
                    <li>Operating system packages (<code class="bg-gray-100 px-1 rounded text-sm">apt update && apt upgrade</code>)</li>
                    <li>Docker images</li>
                    <li>PHP and Laravel dependencies (<code class="bg-gray-100 px-1 rounded text-sm">composer update</code>)</li>
                    <li>Node.js packages if applicable</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Audit & Monitoring -->
    <div class="bg-white border border-gray-200 rounded-lg p-6 mb-8">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">
            <i class="fas fa-eye text-orange-600 mr-2"></i>Audit & Monitoring
        </h2>
        <p class="text-gray-700 mb-4">CAS includes a comprehensive audit log system. Administrators should regularly review:</p>
        <ul class="space-y-3 text-gray-700">
            <li class="flex items-start">
                <i class="fas fa-check text-green-500 mt-1 mr-3"></i>
                <div><strong>Login attempts</strong> — Monitor for unusual login patterns or failed login spikes</div>
            </li>
            <li class="flex items-start">
                <i class="fas fa-check text-green-500 mt-1 mr-3"></i>
                <div><strong>IP whitelist violations</strong> — Check for unauthorized IP access attempts</div>
            </li>
            <li class="flex items-start">
                <i class="fas fa-check text-green-500 mt-1 mr-3"></i>
                <div><strong>Token generation events</strong> — Track SSO token creation and usage</div>
            </li>
            <li class="flex items-start">
                <i class="fas fa-check text-green-500 mt-1 mr-3"></i>
                <div><strong>Admin actions</strong> — User creation, role changes, system configuration modifications</div>
            </li>
        </ul>

        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mt-4">
            <p class="text-blue-800 text-sm"><i class="fas fa-info-circle mr-1"></i> Access audit logs from the Admin Panel under <strong>Audit Logs</strong>. Set up routine weekly reviews as a security best practice.</p>
        </div>
    </div>

    <!-- Incident Response -->
    <div class="bg-white border border-gray-200 rounded-lg p-6 mb-8">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">
            <i class="fas fa-exclamation-triangle text-red-600 mr-2"></i>Potential Breaches & Incident Response
        </h2>
        <p class="text-gray-700 mb-4">If you suspect a security breach:</p>
        <ol class="list-decimal list-inside space-y-2 text-gray-700">
            <li><strong>Immediately rotate</strong> the JWT secret and all client system API secrets</li>
            <li><strong>Review audit logs</strong> to identify the scope and source of the breach</li>
            <li><strong>Revoke</strong> all active sessions by clearing the session store</li>
            <li><strong>Enable IP whitelist</strong> to restrict access to known IPs only</li>
            <li><strong>Force password resets</strong> for all affected user accounts</li>
            <li><strong>Contact the development team</strong> at <a href="https://innovativesolution.com.np/" target="_blank" class="font-semibold text-blue-600 hover:text-blue-800">innovativesolution.com.np</a> for assistance</li>
        </ol>
    </div>

    <!-- Navigation -->
    <div class="flex justify-between items-center border-t border-gray-200 pt-6">
        <a href="{{ route('docs.user-guide') }}" class="flex items-center text-blue-600 hover:text-blue-800">
            <i class="fas fa-arrow-left mr-2"></i>User Guide
        </a>
        <a href="{{ route('docs.troubleshooting') }}" class="flex items-center text-blue-600 hover:text-blue-800">
            Troubleshooting<i class="fas fa-arrow-right ml-2"></i>
        </a>
    </div>
</div>
@endsection
