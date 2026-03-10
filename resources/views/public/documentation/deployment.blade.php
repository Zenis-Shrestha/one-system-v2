@extends('public.documentation.layout')

@section('title', 'Deployment Guide — CAS SSO')
@section('description', 'Deploy CAS SSO to production with Docker, Kubernetes, or traditional hosting.')

@section('content')
<section class="border-b border-slate-200 pb-10 mb-12">
    <div class="max-w-3xl">
        <p class="text-sm font-medium text-blue-600 tracking-wide uppercase mb-3">Advanced Topics</p>
        <h1 class="text-4xl font-extrabold text-slate-900 tracking-tight leading-tight mb-4">Deployment Guide</h1>
        <p class="text-lg text-slate-500 leading-relaxed">Production-ready deployment configurations for CAS SSO.</p>
    </div>
</section>

{{-- Requirements --}}
<section class="mb-12" id="requirements">
    <h2 class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-4">System Requirements</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="p-5 rounded-xl border border-slate-200">
            <h3 class="text-sm font-semibold text-slate-900 mb-3">Server</h3>
            <ul class="space-y-1.5 text-sm text-slate-600">
                <li class="flex items-center gap-2"><i class="fas fa-check text-emerald-500 text-xs"></i>PHP 8.3+ with Laravel 11</li>
                <li class="flex items-center gap-2"><i class="fas fa-check text-emerald-500 text-xs"></i>PostgreSQL 14+</li>
                <li class="flex items-center gap-2"><i class="fas fa-check text-emerald-500 text-xs"></i>Redis 7+ for cache</li>
                <li class="flex items-center gap-2"><i class="fas fa-check text-emerald-500 text-xs"></i>Nginx web server</li>
                <li class="flex items-center gap-2"><i class="fas fa-check text-emerald-500 text-xs"></i>Node.js 20+ for asset compilation</li>
            </ul>
        </div>
        <div class="p-5 rounded-xl border border-slate-200">
            <h3 class="text-sm font-semibold text-slate-900 mb-3">Minimum Resources</h3>
            <ul class="space-y-1.5 text-sm text-slate-600">
                <li class="flex items-center gap-2"><i class="fas fa-memory text-blue-500 text-xs"></i>2 GB RAM</li>
                <li class="flex items-center gap-2"><i class="fas fa-microchip text-blue-500 text-xs"></i>2 vCPU cores</li>
                <li class="flex items-center gap-2"><i class="fas fa-hdd text-blue-500 text-xs"></i>20 GB SSD storage</li>
                <li class="flex items-center gap-2"><i class="fas fa-network-wired text-blue-500 text-xs"></i>SSL/TLS certificate</li>
            </ul>
        </div>
    </div>
</section>

{{-- Docker --}}
<section class="mb-12" id="docker">
    <h2 class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-4">Docker Deployment</h2>
    <p class="text-sm text-slate-600 mb-4">The project includes a multi-service Docker Compose configuration for development and a production overlay.</p>
    <div class="rounded-xl border border-slate-200 overflow-hidden mb-6">
        <div class="flex items-center px-4 py-2.5 bg-slate-50 border-b border-slate-200">
            <i class="fab fa-docker text-blue-500 text-sm mr-2"></i>
            <span class="text-xs font-medium text-slate-600">docker-compose.yml</span>
        </div>
        <div class="bg-slate-900 p-5 overflow-x-auto">
            <pre class="text-sm leading-relaxed font-mono text-slate-300"><code><span class="text-amber-300">version</span>: <span class="text-green-400">'3.8'</span>
<span class="text-amber-300">services</span>:
  <span class="text-amber-300">app</span>:
    <span class="text-amber-300">build</span>:
      <span class="text-amber-300">context</span>: <span class="text-green-400">./docker/nginx</span>
    <span class="text-amber-300">ports</span>:
      - <span class="text-green-400">"80:80"</span>
    <span class="text-amber-300">depends_on</span>:
      - <span class="text-green-400">php</span>
      - <span class="text-green-400">redis</span>

  <span class="text-amber-300">php</span>:
    <span class="text-amber-300">build</span>:
      <span class="text-amber-300">context</span>: <span class="text-green-400">./docker/php</span>
    <span class="text-amber-300">volumes</span>:
      - <span class="text-green-400">.:/var/www/html:delegated</span>
    <span class="text-amber-300">env_file</span>:
      - <span class="text-green-400">.env</span>

  <span class="text-amber-300">redis</span>:
    <span class="text-amber-300">image</span>: <span class="text-green-400">redis:7-alpine</span>
    <span class="text-amber-300">command</span>: <span class="text-green-400">redis-server --appendonly yes --requirepass ${REDIS_PASSWORD}</span>

<span class="text-amber-300">volumes</span>:
  <span class="text-amber-300">redis_data</span>:</code></pre>
        </div>
    </div>

    <div class="rounded-xl border border-slate-200 overflow-hidden mb-6">
        <div class="flex items-center px-4 py-2.5 bg-slate-50 border-b border-slate-200">
            <span class="text-xs font-medium text-slate-600">Terminal — Build & Run</span>
        </div>
        <div class="bg-slate-900 p-5 overflow-x-auto">
            <pre class="text-sm leading-relaxed font-mono text-slate-300"><code><span class="text-slate-500"># Build and start all services</span>
docker-compose up -d --build

<span class="text-slate-500"># Run migrations and seed the database</span>
docker-compose exec php php artisan migrate --seed

<span class="text-slate-500"># Generate application key</span>
docker-compose exec php php artisan key:generate

<span class="text-slate-500"># Build frontend assets</span>
docker-compose run --rm node npm install && npm run build</code></pre>
        </div>
    </div>

    <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-6">
        <div class="flex items-start gap-2">
            <i class="fas fa-info-circle text-blue-500 mt-0.5"></i>
            <div class="text-sm text-blue-800">
                <strong>Production Overlay</strong> — Use <code class="text-xs bg-blue-100 px-1 py-0.5 rounded font-mono">docker-production.yml</code> for production which adds Prometheus monitoring, Grafana dashboards, Fluentd logging, and queue workers with resource limits.
            </div>
        </div>
    </div>
</section>

{{-- Kubernetes --}}
<section class="mb-12" id="kubernetes">
    <h2 class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-4">Kubernetes Deployment</h2>
    <p class="text-sm text-slate-600 mb-4">Deploy CAS SSO on Kubernetes using the standalone Dockerfile. The Dockerfile bundles PHP-FPM, Nginx, and Supervisor into a single image.</p>
    <div class="rounded-xl border border-slate-200 overflow-hidden mb-6">
        <div class="flex items-center px-4 py-2.5 bg-slate-50 border-b border-slate-200">
            <span class="text-xs font-medium text-slate-600">k8s/deployment.yaml</span>
        </div>
        <div class="bg-slate-900 p-5 overflow-x-auto">
            <pre class="text-sm leading-relaxed font-mono text-slate-300"><code><span class="text-amber-300">apiVersion</span>: apps/v1
<span class="text-amber-300">kind</span>: Deployment
<span class="text-amber-300">metadata</span>:
  <span class="text-amber-300">name</span>: cas-sso
<span class="text-amber-300">spec</span>:
  <span class="text-amber-300">replicas</span>: <span class="text-blue-400">2</span>
  <span class="text-amber-300">selector</span>:
    <span class="text-amber-300">matchLabels</span>:
      <span class="text-amber-300">app</span>: cas-sso
  <span class="text-amber-300">template</span>:
    <span class="text-amber-300">metadata</span>:
      <span class="text-amber-300">labels</span>:
        <span class="text-amber-300">app</span>: cas-sso
    <span class="text-amber-300">spec</span>:
      <span class="text-amber-300">containers</span>:
        - <span class="text-amber-300">name</span>: cas-sso
          <span class="text-amber-300">image</span>: <span class="text-green-400">your-registry/cas-sso:latest</span>
          <span class="text-amber-300">ports</span>:
            - <span class="text-amber-300">containerPort</span>: <span class="text-blue-400">80</span>
          <span class="text-amber-300">envFrom</span>:
            - <span class="text-amber-300">secretRef</span>:
                <span class="text-amber-300">name</span>: cas-sso-secrets
          <span class="text-amber-300">resources</span>:
            <span class="text-amber-300">requests</span>:
              <span class="text-amber-300">cpu</span>: <span class="text-green-400">"500m"</span>
              <span class="text-amber-300">memory</span>: <span class="text-green-400">"512Mi"</span>
            <span class="text-amber-300">limits</span>:
              <span class="text-amber-300">cpu</span>: <span class="text-green-400">"1000m"</span>
              <span class="text-amber-300">memory</span>: <span class="text-green-400">"1Gi"</span>
          <span class="text-amber-300">livenessProbe</span>:
            <span class="text-amber-300">httpGet</span>:
              <span class="text-amber-300">path</span>: /health
              <span class="text-amber-300">port</span>: <span class="text-blue-400">80</span>
            <span class="text-amber-300">initialDelaySeconds</span>: <span class="text-blue-400">10</span>
            <span class="text-amber-300">periodSeconds</span>: <span class="text-blue-400">30</span></code></pre>
        </div>
    </div>

    <div class="rounded-xl border border-slate-200 overflow-hidden">
        <div class="flex items-center px-4 py-2.5 bg-slate-50 border-b border-slate-200">
            <span class="text-xs font-medium text-slate-600">Terminal — Build & Deploy</span>
        </div>
        <div class="bg-slate-900 p-5 overflow-x-auto">
            <pre class="text-sm leading-relaxed font-mono text-slate-300"><code><span class="text-slate-500"># Build the production image (uses root Dockerfile)</span>
docker build -t your-registry/cas-sso:latest .

<span class="text-slate-500"># Push to registry</span>
docker push your-registry/cas-sso:latest

<span class="text-slate-500"># Deploy to cluster</span>
kubectl apply -f k8s/deployment.yaml</code></pre>
        </div>
    </div>
</section>

{{-- Environment --}}
<section class="mb-12" id="environment">
    <h2 class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-4">Environment Configuration</h2>
    <div class="rounded-xl border border-slate-200 overflow-hidden">
        <div class="flex items-center px-4 py-2.5 bg-slate-50 border-b border-slate-200">
            <span class="text-xs font-medium text-slate-600">.env (production)</span>
        </div>
        <div class="bg-slate-900 p-5 overflow-x-auto">
            <pre class="text-sm leading-relaxed font-mono text-slate-300"><code><span class="text-amber-300">APP_NAME</span>=<span class="text-green-400">"One System"</span>
<span class="text-amber-300">APP_ENV</span>=<span class="text-green-400">production</span>
<span class="text-amber-300">APP_DEBUG</span>=<span class="text-green-400">false</span>
<span class="text-amber-300">APP_URL</span>=<span class="text-green-400">https://cas.yourdomain.com</span>

<span class="text-amber-300">DB_CONNECTION</span>=<span class="text-green-400">pgsql</span>
<span class="text-amber-300">DB_HOST</span>=<span class="text-green-400">your_db_host</span>
<span class="text-amber-300">DB_DATABASE</span>=<span class="text-green-400">cas_system</span>
<span class="text-amber-300">DB_USERNAME</span>=<span class="text-green-400">cas_user</span>
<span class="text-amber-300">DB_PASSWORD</span>=<span class="text-green-400">your_secure_password</span>

<span class="text-amber-300">SESSION_DRIVER</span>=<span class="text-green-400">database</span>
<span class="text-amber-300">CACHE_STORE</span>=<span class="text-green-400">database</span>
<span class="text-amber-300">QUEUE_CONNECTION</span>=<span class="text-green-400">database</span>

<span class="text-amber-300">REDIS_HOST</span>=<span class="text-green-400">your_redis_host</span>
<span class="text-amber-300">REDIS_PASSWORD</span>=<span class="text-green-400">your_redis_password</span>

<span class="text-amber-300">RECAPTCHA_SITE_KEY</span>=<span class="text-green-400">your_recaptcha_site_key</span>
<span class="text-amber-300">RECAPTCHA_SECRET_KEY</span>=<span class="text-green-400">your_recaptcha_secret</span></code></pre>
        </div>
    </div>
</section>

{{-- Production Checklist --}}
<section class="border-t border-slate-200 pt-10">
    <h2 class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-4">Production Checklist</h2>
    <div class="space-y-2">
        <div class="flex items-center gap-3 p-3 rounded-lg border border-slate-200 text-sm">
            <i class="fas fa-check-square text-emerald-500"></i><span class="text-slate-700">Set <code class="text-xs bg-slate-100 px-1 py-0.5 rounded font-mono">APP_DEBUG=false</code></span>
        </div>
        <div class="flex items-center gap-3 p-3 rounded-lg border border-slate-200 text-sm">
            <i class="fas fa-check-square text-emerald-500"></i><span class="text-slate-700">Configure SSL/TLS with valid certificate</span>
        </div>
        <div class="flex items-center gap-3 p-3 rounded-lg border border-slate-200 text-sm">
            <i class="fas fa-check-square text-emerald-500"></i><span class="text-slate-700">Set strong database and Redis passwords</span>
        </div>
        <div class="flex items-center gap-3 p-3 rounded-lg border border-slate-200 text-sm">
            <i class="fas fa-check-square text-emerald-500"></i><span class="text-slate-700">Run <code class="text-xs bg-slate-100 px-1 py-0.5 rounded font-mono">php artisan config:cache</code> and <code class="text-xs bg-slate-100 px-1 py-0.5 rounded font-mono">php artisan route:cache</code></span>
        </div>
        <div class="flex items-center gap-3 p-3 rounded-lg border border-slate-200 text-sm">
            <i class="fas fa-check-square text-emerald-500"></i><span class="text-slate-700">Configure reCAPTCHA keys for production domain</span>
        </div>
        <div class="flex items-center gap-3 p-3 rounded-lg border border-slate-200 text-sm">
            <i class="fas fa-check-square text-emerald-500"></i><span class="text-slate-700">Set up automated database backups</span>
        </div>
        <div class="flex items-center gap-3 p-3 rounded-lg border border-slate-200 text-sm">
            <i class="fas fa-check-square text-emerald-500"></i><span class="text-slate-700">Enable application-level logging with daily rotation</span>
        </div>
        <div class="flex items-center gap-3 p-3 rounded-lg border border-slate-200 text-sm">
            <i class="fas fa-check-square text-emerald-500"></i><span class="text-slate-700">Configure IP whitelist for all client systems</span>
        </div>
    </div>
</section>
@endsection