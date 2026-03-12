@extends('public.documentation.layout')

@section('title', 'Python Django Integration — CAS SSO')
@section('description', 'Guide for integrating Django apps with CAS SSO.')

@section('content')
<section class="border-b border-slate-200 pb-10 mb-12">
    <div class="max-w-3xl">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center"><i class="fab fa-python text-indigo-600 text-lg"></i></div>
            <div>
                <p class="text-sm font-medium text-blue-600 tracking-wide uppercase">Integration Guide</p>
                <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Python / Django</h1>
            </div>
        </div>
        <p class="text-lg text-slate-500 leading-relaxed mb-4">{{ $pythonGuide['description'] }}</p>
        <div class="flex flex-wrap gap-4 text-xs text-slate-500">
            <span><i class="fas fa-clock mr-1"></i>7 min</span>
            <span><i class="fas fa-signal mr-1"></i>Intermediate</span>
            <span><i class="fas fa-tag mr-1"></i>Python 3.9+</span>
        </div>
    </div>
</section>

<nav class="mb-12 p-5 rounded-xl border border-slate-200 bg-slate-50/50">
    <h2 class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-3">On This Page</h2>
    <ol class="space-y-1.5 text-sm">
        <li><a href="#install" class="text-blue-600">1. Installation</a></li>
        <li><a href="#settings" class="text-blue-600">2. Django Settings</a></li>
        <li><a href="#middleware" class="text-blue-600">3. Middleware</a></li>
        <li><a href="#views" class="text-blue-600">4. Views</a></li>
    </ol>
</nav>

<section id="install" class="mb-12">
    <h2 class="text-xl font-bold text-slate-900 mb-4">1. Installation</h2>
    <div class="rounded-xl border border-slate-200 overflow-hidden">
        <div class="bg-slate-900 p-5"><pre class="text-sm font-mono text-slate-300"><code>pip install cas-sso-client requests PyJWT</code></pre></div>
    </div>
</section>

<section id="settings" class="mb-12">
    <h2 class="text-xl font-bold text-slate-900 mb-4">2. Django Settings</h2>
    <div class="rounded-xl border border-slate-200 overflow-hidden">
        <div class="flex items-center px-4 py-2.5 bg-slate-50 border-b border-slate-200"><span class="text-xs font-medium text-slate-600">settings.py</span></div>
        <div class="bg-slate-900 p-5 overflow-x-auto">
            <pre class="text-sm font-mono text-slate-300"><code>CAS_CONFIG = {
    'SERVER_URL':    os.environ.get('CAS_SERVER_URL'),
    'CLIENT_ID':     os.environ.get('CAS_CLIENT_ID'),
    'CLIENT_SECRET': os.environ.get('CAS_CLIENT_SECRET'),
    'CALLBACK_URL':  os.environ.get('CAS_CALLBACK_URL'),
}

MIDDLEWARE = [
    # ...
    'cas_client.middleware.CasAuthMiddleware',
]</code></pre>
        </div>
    </div>
</section>

<section id="middleware" class="mb-12">
    <h2 class="text-xl font-bold text-slate-900 mb-4">3. Middleware</h2>
    <div class="rounded-xl border border-slate-200 overflow-hidden">
        <div class="flex items-center px-4 py-2.5 bg-slate-50 border-b border-slate-200"><span class="text-xs font-medium text-slate-600">cas_client/middleware.py</span></div>
        <div class="bg-slate-900 p-5 overflow-x-auto">
            <pre class="text-sm font-mono text-slate-300"><code>import requests
from django.conf import settings
from django.http import JsonResponse

class CasAuthMiddleware:
    def __init__(self, get_response):
        self.get_response = get_response
        self.config = settings.CAS_CONFIG

    def __call__(self, request):
        if request.path.startswith('/cas/'):
            return self.get_response(request)

        token = request.headers.get('Authorization', '').replace('Bearer ', '')
        if not token:
            return JsonResponse({'error': 'Token required'}, status=401)

        resp = requests.post(
            f"{self.config['SERVER_URL']}/api/sso/validate",
            json={
                'token': token,
                'client_id': self.config['CLIENT_ID'],
                'client_secret': self.config['CLIENT_SECRET'],
            }
        )

        if resp.status_code == 200:
            request.cas_user = resp.json()['user']
            return self.get_response(request)

        return JsonResponse({'error': 'Invalid token'}, status=401)</code></pre>
        </div>
    </div>
</section>

<section id="views" class="mb-12">
    <h2 class="text-xl font-bold text-slate-900 mb-4">4. View Protection</h2>
    <div class="rounded-xl border border-slate-200 overflow-hidden mb-6">
        <div class="flex items-center px-4 py-2.5 bg-slate-50 border-b border-slate-200"><span class="text-xs font-medium text-slate-600">views.py</span></div>
        <div class="bg-slate-900 p-5 overflow-x-auto">
            <pre class="text-sm font-mono text-slate-300"><code>from django.http import JsonResponse

def dashboard(request):
    return JsonResponse({'user': request.cas_user})</code></pre>
        </div>
    </div>
    <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4">
        <div class="flex items-start gap-2">
            <i class="fas fa-check-circle text-emerald-500 mt-0.5"></i>
            <span class="text-sm text-emerald-800"><strong>Done!</strong> Access user via <code class="text-xs bg-emerald-100 px-1 py-0.5 rounded font-mono">request.cas_user</code>.</span>
        </div>
    </div>
</section>

<section class="border-t border-slate-200 pt-10">
    <h2 class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-4">Next Steps</h2>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <a href="{{ route('docs.api.overview') }}" class="group flex items-center gap-3 p-4 rounded-lg border border-slate-200 hover:bg-slate-50 transition-all"><i class="fas fa-code text-slate-400 text-sm"></i><span class="text-sm font-medium text-slate-700">API Reference</span></a>
        <a href="{{ route('docs.security') }}" class="group flex items-center gap-3 p-4 rounded-lg border border-slate-200 hover:bg-slate-50 transition-all"><i class="fas fa-shield-alt text-slate-400 text-sm"></i><span class="text-sm font-medium text-slate-700">Security Guide</span></a>
        <a href="{{ route('docs.deployment') }}" class="group flex items-center gap-3 p-4 rounded-lg border border-slate-200 hover:bg-slate-50 transition-all"><i class="fas fa-server text-slate-400 text-sm"></i><span class="text-sm font-medium text-slate-700">Deployment</span></a>
    </div>
</section>
@endsection
