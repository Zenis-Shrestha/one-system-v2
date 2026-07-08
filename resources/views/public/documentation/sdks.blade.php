@extends('public.documentation.layout')

@section('title', 'SDKs & packages — One System SSO')
@section('description', 'Official SDKs and client packages for integrating with the One System single sign-on service.')

@section('content')
<section class="border-b border-[var(--color-line)] pb-10 mb-12">
    <div class="">
        <p class="os-eyebrow mb-3">Technical reference</p>
        <h1 class="text-4xl font-semibold text-[var(--color-ink)] tracking-tight leading-tight mb-4">SDKs &amp; packages</h1>
        <p class="text-lg text-[var(--color-muted)] leading-relaxed">Official client libraries maintained by the One System team. Each SDK handles the SSO redirect, server-to-server token validation, and session management.</p>
    </div>
</section>

{{-- SDK grid --}}
<section class="mb-16">
    <h2 class="os-eyebrow text-[var(--color-muted)] mb-6">Official SDKs</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

        {{-- Laravel --}}
        <div class="os-card os-card-pad os-card-hover">
            <div class="flex items-center gap-3 mb-4">
                <div class="os-icon-tile os-icon-tile-ink"><i class="fab fa-laravel"></i></div>
                <div>
                    <h3 class="text-sm font-semibold text-[var(--color-ink)]">Laravel client</h3>
                    <p class="text-xs text-[var(--color-faint)] font-mono">one-system/laravel-sso-client</p>
                </div>
                <span class="os-badge ml-auto">v2.1.0</span>
            </div>
            <div class="os-codeblock mb-3">
                <pre><code>composer require one-system/laravel-sso-client</code></pre>
            </div>
            <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-[var(--color-muted)]">
                <span>Laravel 10+</span>
                <span>&middot;</span>
                <span>PHP 8.1+</span>
                <span>&middot;</span>
                <a href="{{ route('docs.laravel') }}" class="text-[var(--color-accent)] hover:text-[var(--color-accent-strong)] font-medium">View guide &rarr;</a>
                <span>&middot;</span>
                <a href="/downloads/one-system-client-package.zip" class="text-[var(--color-accent)] hover:text-[var(--color-accent-strong)] font-medium">Download .zip &darr;</a>
            </div>
        </div>

        {{-- Node.js --}}
        <div class="os-card os-card-pad os-card-hover">
            <div class="flex items-center gap-3 mb-4">
                <div class="os-icon-tile os-icon-tile-ink"><i class="fab fa-node-js"></i></div>
                <div>
                    <h3 class="text-sm font-semibold text-[var(--color-ink)]">Node.js SDK</h3>
                    <p class="text-xs text-[var(--color-faint)] font-mono">@cas-system/node-sso-client</p>
                </div>
                <span class="os-badge ml-auto">v2.0.3</span>
            </div>
            <div class="os-codeblock mb-3">
                <pre><code>npm install @cas-system/node-sso-client</code></pre>
            </div>
            <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-[var(--color-muted)]">
                <span>Node 18+</span>
                <span>&middot;</span>
                <span>Express / Koa</span>
                <span>&middot;</span>
                <a href="{{ route('docs.nodejs') }}" class="text-[var(--color-accent)] hover:text-[var(--color-accent-strong)] font-medium">View guide &rarr;</a>
                <span>&middot;</span>
                <a href="/downloads/nodejs-cas-client.zip" class="text-[var(--color-accent)] hover:text-[var(--color-accent-strong)] font-medium">Download .zip &darr;</a>
            </div>
        </div>

        {{-- Python --}}
        <div class="os-card os-card-pad os-card-hover">
            <div class="flex items-center gap-3 mb-4">
                <div class="os-icon-tile os-icon-tile-ink"><i class="fab fa-python"></i></div>
                <div>
                    <h3 class="text-sm font-semibold text-[var(--color-ink)]">Python SDK</h3>
                    <p class="text-xs text-[var(--color-faint)] font-mono">one-system-sso-client</p>
                </div>
                <span class="os-badge ml-auto">v2.0.1</span>
            </div>
            <div class="os-codeblock mb-3">
                <pre><code>pip install one-system-sso-client</code></pre>
            </div>
            <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-[var(--color-muted)]">
                <span>Python 3.9+</span>
                <span>&middot;</span>
                <span>Django / Flask</span>
                <span>&middot;</span>
                <a href="{{ route('docs.python') }}" class="text-[var(--color-accent)] hover:text-[var(--color-accent-strong)] font-medium">View guide &rarr;</a>
                <span>&middot;</span>
                <a href="/downloads/python-cas-client.zip" class="text-[var(--color-accent)] hover:text-[var(--color-accent-strong)] font-medium">Download .zip &darr;</a>
            </div>
        </div>

        {{-- .NET --}}
        <div class="os-card os-card-pad os-card-hover">
            <div class="flex items-center gap-3 mb-4">
                <div class="os-icon-tile os-icon-tile-ink"><i class="fab fa-microsoft"></i></div>
                <div>
                    <h3 class="text-sm font-semibold text-[var(--color-ink)]">.NET client</h3>
                    <p class="text-xs text-[var(--color-faint)] font-mono">OneSystem.SsoClient</p>
                </div>
                <span class="os-badge ml-auto">v2.0.0</span>
            </div>
            <div class="os-codeblock mb-3">
                <pre><code>dotnet add package OneSystem.SsoClient</code></pre>
            </div>
            <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-[var(--color-muted)]">
                <span>.NET 8+</span>
                <span>&middot;</span>
                <span>ASP.NET MVC</span>
                <span>&middot;</span>
                <a href="{{ route('docs.dotnet') }}" class="text-[var(--color-accent)] hover:text-[var(--color-accent-strong)] font-medium">View guide &rarr;</a>
                <span>&middot;</span>
                <a href="/downloads/dotnet-cas-client.zip" class="text-[var(--color-accent)] hover:text-[var(--color-accent-strong)] font-medium">Download .zip &darr;</a>
            </div>
        </div>

        {{-- Java --}}
        <div class="os-card os-card-pad os-card-hover">
            <div class="flex items-center gap-3 mb-4">
                <div class="os-icon-tile os-icon-tile-ink"><i class="fab fa-java"></i></div>
                <div>
                    <h3 class="text-sm font-semibold text-[var(--color-ink)]">Java SDK</h3>
                    <p class="text-xs text-[var(--color-faint)] font-mono">com.onesystem:sso-client</p>
                </div>
                <span class="os-badge ml-auto">v2.0.0</span>
            </div>
            <div class="os-codeblock mb-3">
                <pre><code>&lt;dependency&gt;com.onesystem:sso-client:2.0.0&lt;/dependency&gt;</code></pre>
            </div>
            <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-[var(--color-muted)]">
                <span>Java 17+</span>
                <span>&middot;</span>
                <span>Spring Boot</span>
                <span>&middot;</span>
                <a href="{{ route('docs.java') }}" class="text-[var(--color-accent)] hover:text-[var(--color-accent-strong)] font-medium">View guide &rarr;</a>
                <span>&middot;</span>
                <a href="/downloads/java-cas-client.zip" class="text-[var(--color-accent)] hover:text-[var(--color-accent-strong)] font-medium">Download .zip &darr;</a>
            </div>
        </div>

        {{-- JavaScript --}}
        <div class="os-card os-card-pad os-card-hover">
            <div class="flex items-center gap-3 mb-4">
                <div class="os-icon-tile os-icon-tile-ink"><i class="fab fa-js"></i></div>
                <div>
                    <h3 class="text-sm font-semibold text-[var(--color-ink)]">JavaScript SDK</h3>
                    <p class="text-xs text-[var(--color-faint)] font-mono">@cas-system/js-sso-client</p>
                </div>
                <span class="os-badge ml-auto">v2.0.0</span>
            </div>
            <div class="os-codeblock mb-3">
                <pre><code>&lt;script src="https://cdn.one-system.example.com/js/v2.js"&gt;&lt;/script&gt;</code></pre>
            </div>
            <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-[var(--color-muted)]">
                <span>Browser / SPA</span>
                <span>&middot;</span>
                <span>CDN or npm</span>
                <span>&middot;</span>
                <a href="{{ route('docs.javascript') }}" class="text-[var(--color-accent)] hover:text-[var(--color-accent-strong)] font-medium">View guide &rarr;</a>
                <span>&middot;</span>
                <a href="/downloads/javascript-cas-client.zip" class="text-[var(--color-accent)] hover:text-[var(--color-accent-strong)] font-medium">Download .zip &darr;</a>
            </div>
        </div>
    </div>

    <div class="os-alert mt-5">
        <i class="fas fa-circle-info text-[var(--color-accent)] mt-0.5"></i>
        <span>The browser SDK only starts the redirect flow. Token validation requires the <code class="os-code-inline">client_secret</code> and must always run on your server &mdash; never embed the secret in client-side code.</span>
    </div>
</section>

{{-- Compatibility matrix --}}
<section class="border-t border-[var(--color-line)] pt-10">
    <h2 class="os-eyebrow text-[var(--color-muted)] mb-6">Compatibility matrix</h2>
    <div class="os-card overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-[var(--color-surface-2)] border-b border-[var(--color-line)]">
                <tr>
                    <th class="text-left px-5 py-3 font-semibold text-[var(--color-ink-2)]">SDK</th>
                    <th class="text-left px-5 py-3 font-semibold text-[var(--color-ink-2)]">Min runtime</th>
                    <th class="text-left px-5 py-3 font-semibold text-[var(--color-ink-2)]">Package manager</th>
                    <th class="text-left px-5 py-3 font-semibold text-[var(--color-ink-2)]">One System server</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[var(--color-line)]">
                <tr>
                    <td class="px-5 py-3 font-medium text-[var(--color-ink)]">Laravel</td>
                    <td class="px-5 py-3 text-[var(--color-ink-2)]">PHP 8.1 / Laravel 10</td>
                    <td class="px-5 py-3 text-[var(--color-muted)]">Composer</td>
                    <td class="px-5 py-3 text-[var(--color-muted)]">v2.0+</td>
                </tr>
                <tr>
                    <td class="px-5 py-3 font-medium text-[var(--color-ink)]">Node.js</td>
                    <td class="px-5 py-3 text-[var(--color-ink-2)]">Node 18</td>
                    <td class="px-5 py-3 text-[var(--color-muted)]">npm / yarn</td>
                    <td class="px-5 py-3 text-[var(--color-muted)]">v2.0+</td>
                </tr>
                <tr>
                    <td class="px-5 py-3 font-medium text-[var(--color-ink)]">Python</td>
                    <td class="px-5 py-3 text-[var(--color-ink-2)]">Python 3.9</td>
                    <td class="px-5 py-3 text-[var(--color-muted)]">pip</td>
                    <td class="px-5 py-3 text-[var(--color-muted)]">v2.0+</td>
                </tr>
                <tr>
                    <td class="px-5 py-3 font-medium text-[var(--color-ink)]">.NET</td>
                    <td class="px-5 py-3 text-[var(--color-ink-2)]">.NET 8</td>
                    <td class="px-5 py-3 text-[var(--color-muted)]">NuGet</td>
                    <td class="px-5 py-3 text-[var(--color-muted)]">v2.0+</td>
                </tr>
                <tr>
                    <td class="px-5 py-3 font-medium text-[var(--color-ink)]">Java</td>
                    <td class="px-5 py-3 text-[var(--color-ink-2)]">Java 17 / Spring 3</td>
                    <td class="px-5 py-3 text-[var(--color-muted)]">Maven / Gradle</td>
                    <td class="px-5 py-3 text-[var(--color-muted)]">v2.0+</td>
                </tr>
                <tr>
                    <td class="px-5 py-3 font-medium text-[var(--color-ink)]">JavaScript</td>
                    <td class="px-5 py-3 text-[var(--color-ink-2)]">ES2020+</td>
                    <td class="px-5 py-3 text-[var(--color-muted)]">CDN / npm</td>
                    <td class="px-5 py-3 text-[var(--color-muted)]">v2.0+</td>
                </tr>
            </tbody>
        </table>
    </div>
</section>
@endsection
