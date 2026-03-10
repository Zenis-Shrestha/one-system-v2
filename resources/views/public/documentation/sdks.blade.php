@extends('public.documentation.layout')

@section('title', 'SDKs & Packages — CAS SSO')
@section('description', 'Official SDKs and client packages for integrating with CAS Single Sign-On authentication system.')

@section('content')
<section class="border-b border-slate-200 pb-10 mb-12">
    <div class="max-w-3xl">
        <p class="text-sm font-medium text-blue-600 tracking-wide uppercase mb-3">Technical Reference</p>
        <h1 class="text-4xl font-extrabold text-slate-900 tracking-tight leading-tight mb-4">SDKs &amp; Packages</h1>
        <p class="text-lg text-slate-500 leading-relaxed">Official client libraries maintained by the CAS team. Each SDK handles token generation, validation, and session management.</p>
    </div>
</section>

{{-- SDK Grid --}}
<section class="mb-16">
    <h2 class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-6">Official SDKs</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

        {{-- Laravel --}}
        <div class="p-6 rounded-xl border border-slate-200 hover:border-red-200 transition-colors">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
                    <i class="fab fa-laravel text-red-600"></i>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-slate-900">Laravel Client</h3>
                    <p class="text-xs text-slate-400">cas-system/laravel-client</p>
                </div>
                <span class="ml-auto text-xs font-medium bg-slate-100 text-slate-600 px-2 py-0.5 rounded">v2.1.0</span>
            </div>
            <div class="rounded-lg bg-slate-900 p-3 mb-3">
                <code class="text-xs text-slate-300 font-mono">composer require cas-system/laravel-client</code>
            </div>
            <div class="flex items-center gap-3 text-xs text-slate-500">
                <span>Laravel 10+</span>
                <span>&middot;</span>
                <span>PHP 8.1+</span>
                <span>&middot;</span>
                <a href="{{ route('docs.laravel') }}" class="text-blue-600 hover:text-blue-800 font-medium">View Guide &rarr;</a>
            </div>
        </div>

        {{-- Node.js --}}
        <div class="p-6 rounded-xl border border-slate-200 hover:border-green-200 transition-colors">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fab fa-node-js text-green-600"></i>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-slate-900">Node.js SDK</h3>
                    <p class="text-xs text-slate-400">@cas-system/node-client</p>
                </div>
                <span class="ml-auto text-xs font-medium bg-slate-100 text-slate-600 px-2 py-0.5 rounded">v2.0.3</span>
            </div>
            <div class="rounded-lg bg-slate-900 p-3 mb-3">
                <code class="text-xs text-slate-300 font-mono">npm install @cas-system/node-client</code>
            </div>
            <div class="flex items-center gap-3 text-xs text-slate-500">
                <span>Node 18+</span>
                <span>&middot;</span>
                <span>Express / Koa</span>
                <span>&middot;</span>
                <a href="{{ route('docs.nodejs') }}" class="text-blue-600 hover:text-blue-800 font-medium">View Guide &rarr;</a>
            </div>
        </div>

        {{-- Python --}}
        <div class="p-6 rounded-xl border border-slate-200 hover:border-indigo-200 transition-colors">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                    <i class="fab fa-python text-indigo-600"></i>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-slate-900">Python SDK</h3>
                    <p class="text-xs text-slate-400">cas-sso-client</p>
                </div>
                <span class="ml-auto text-xs font-medium bg-slate-100 text-slate-600 px-2 py-0.5 rounded">v2.0.1</span>
            </div>
            <div class="rounded-lg bg-slate-900 p-3 mb-3">
                <code class="text-xs text-slate-300 font-mono">pip install cas-sso-client</code>
            </div>
            <div class="flex items-center gap-3 text-xs text-slate-500">
                <span>Python 3.9+</span>
                <span>&middot;</span>
                <span>Django / Flask</span>
                <span>&middot;</span>
                <a href="{{ route('docs.python') }}" class="text-blue-600 hover:text-blue-800 font-medium">View Guide &rarr;</a>
            </div>
        </div>

        {{-- .NET --}}
        <div class="p-6 rounded-xl border border-slate-200 hover:border-blue-200 transition-colors">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fab fa-microsoft text-blue-600"></i>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-slate-900">.NET Client</h3>
                    <p class="text-xs text-slate-400">CasSystem.Client</p>
                </div>
                <span class="ml-auto text-xs font-medium bg-slate-100 text-slate-600 px-2 py-0.5 rounded">v2.0.0</span>
            </div>
            <div class="rounded-lg bg-slate-900 p-3 mb-3">
                <code class="text-xs text-slate-300 font-mono">dotnet add package CasSystem.Client</code>
            </div>
            <div class="flex items-center gap-3 text-xs text-slate-500">
                <span>.NET 6+</span>
                <span>&middot;</span>
                <span>ASP.NET MVC</span>
                <span>&middot;</span>
                <a href="{{ route('docs.dotnet') }}" class="text-blue-600 hover:text-blue-800 font-medium">View Guide &rarr;</a>
            </div>
        </div>

        {{-- Java --}}
        <div class="p-6 rounded-xl border border-slate-200 hover:border-orange-200 transition-colors">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                    <i class="fab fa-java text-orange-600"></i>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-slate-900">Java SDK</h3>
                    <p class="text-xs text-slate-400">com.cas-system:java-client</p>
                </div>
                <span class="ml-auto text-xs font-medium bg-slate-100 text-slate-600 px-2 py-0.5 rounded">v2.0.0</span>
            </div>
            <div class="rounded-lg bg-slate-900 p-3 mb-3">
                <code class="text-xs text-slate-300 font-mono">&lt;dependency&gt;com.cas-system:java-client:2.0.0&lt;/dependency&gt;</code>
            </div>
            <div class="flex items-center gap-3 text-xs text-slate-500">
                <span>Java 17+</span>
                <span>&middot;</span>
                <span>Spring Boot</span>
                <span>&middot;</span>
                <a href="{{ route('docs.java') }}" class="text-blue-600 hover:text-blue-800 font-medium">View Guide &rarr;</a>
            </div>
        </div>

        {{-- JavaScript --}}
        <div class="p-6 rounded-xl border border-slate-200 hover:border-yellow-200 transition-colors">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center">
                    <i class="fab fa-js text-yellow-600"></i>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-slate-900">JavaScript SDK</h3>
                    <p class="text-xs text-slate-400">@cas-system/js-client</p>
                </div>
                <span class="ml-auto text-xs font-medium bg-slate-100 text-slate-600 px-2 py-0.5 rounded">v2.0.0</span>
            </div>
            <div class="rounded-lg bg-slate-900 p-3 mb-3">
                <code class="text-xs text-slate-300 font-mono">&lt;script src="https://cdn.cas-system.com/js/v2.js"&gt;&lt;/script&gt;</code>
            </div>
            <div class="flex items-center gap-3 text-xs text-slate-500">
                <span>Browser / SPA</span>
                <span>&middot;</span>
                <span>CDN or npm</span>
                <span>&middot;</span>
                <a href="{{ route('docs.javascript') }}" class="text-blue-600 hover:text-blue-800 font-medium">View Guide &rarr;</a>
            </div>
        </div>
    </div>
</section>

{{-- Compatibility Matrix --}}
<section class="border-t border-slate-200 pt-10">
    <h2 class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-6">Compatibility Matrix</h2>
    <div class="rounded-xl border border-slate-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="text-left px-5 py-3 font-semibold text-slate-600">SDK</th>
                    <th class="text-left px-5 py-3 font-semibold text-slate-600">Min Runtime</th>
                    <th class="text-left px-5 py-3 font-semibold text-slate-600">Package Manager</th>
                    <th class="text-left px-5 py-3 font-semibold text-slate-600">CAS Server</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <tr>
                    <td class="px-5 py-3 font-medium text-slate-900">Laravel</td>
                    <td class="px-5 py-3 text-slate-600">PHP 8.1 / Laravel 10</td>
                    <td class="px-5 py-3 text-slate-500">Composer</td>
                    <td class="px-5 py-3 text-slate-500">v2.0+</td>
                </tr>
                <tr>
                    <td class="px-5 py-3 font-medium text-slate-900">Node.js</td>
                    <td class="px-5 py-3 text-slate-600">Node 18</td>
                    <td class="px-5 py-3 text-slate-500">npm / yarn</td>
                    <td class="px-5 py-3 text-slate-500">v2.0+</td>
                </tr>
                <tr>
                    <td class="px-5 py-3 font-medium text-slate-900">Python</td>
                    <td class="px-5 py-3 text-slate-600">Python 3.9</td>
                    <td class="px-5 py-3 text-slate-500">pip</td>
                    <td class="px-5 py-3 text-slate-500">v2.0+</td>
                </tr>
                <tr>
                    <td class="px-5 py-3 font-medium text-slate-900">.NET</td>
                    <td class="px-5 py-3 text-slate-600">.NET 6</td>
                    <td class="px-5 py-3 text-slate-500">NuGet</td>
                    <td class="px-5 py-3 text-slate-500">v2.0+</td>
                </tr>
                <tr>
                    <td class="px-5 py-3 font-medium text-slate-900">Java</td>
                    <td class="px-5 py-3 text-slate-600">Java 17 / Spring 3</td>
                    <td class="px-5 py-3 text-slate-500">Maven / Gradle</td>
                    <td class="px-5 py-3 text-slate-500">v2.0+</td>
                </tr>
                <tr>
                    <td class="px-5 py-3 font-medium text-slate-900">JavaScript</td>
                    <td class="px-5 py-3 text-slate-600">ES2020+</td>
                    <td class="px-5 py-3 text-slate-500">CDN / npm</td>
                    <td class="px-5 py-3 text-slate-500">v2.0+</td>
                </tr>
            </tbody>
        </table>
    </div>
</section>
@endsection
