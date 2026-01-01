@extends('public.documentation.layout')

@section('title', $deploymentGuide['title'] . ' - CAS Documentation')
@section('description', $deploymentGuide['description'])

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-4">{{ $deploymentGuide['title'] }}</h1>
        <p class="text-lg text-gray-600">{{ $deploymentGuide['description'] }}</p>
    </div>

    <!-- Quick Navigation -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-8">
        <h2 class="text-lg font-semibold text-blue-900 mb-4">Quick Navigation</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            @foreach($deploymentGuide['sections'] as $key => $title)
                <a href="#{{ $key }}" class="text-blue-700 hover:text-blue-900 hover:underline">
                    <i class="fas fa-arrow-right mr-2"></i>{{ $title }}
                </a>
            @endforeach
        </div>
    </div>

    <!-- System Requirements -->
    <section id="requirements" class="mb-12">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">System Requirements</h2>
        
        <div class="bg-gray-50 border border-gray-200 rounded-lg p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Minimum Requirements</h3>
            <ul class="space-y-2 text-gray-700">
                <li><i class="fas fa-server text-blue-600 mr-2"></i><strong>CPU:</strong> 2 vCPUs (4 vCPUs recommended)</li>
                <li><i class="fas fa-memory text-blue-600 mr-2"></i><strong>RAM:</strong> 4GB (8GB recommended)</li>
                <li><i class="fas fa-hdd text-blue-600 mr-2"></i><strong>Storage:</strong> 50GB SSD</li>
                <li><i class="fas fa-network-wired text-blue-600 mr-2"></i><strong>Network:</strong> 1Gbps connection</li>
            </ul>
        </div>

        <div class="bg-gray-50 border border-gray-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Software Dependencies</h3>
            <ul class="space-y-2 text-gray-700">
                <li><i class="fab fa-php text-blue-600 mr-2"></i><strong>PHP:</strong> 8.2+ with extensions (pdo, pgsql, bcmath, redis)</li>
                <li><i class="fas fa-database text-blue-600 mr-2"></i><strong>PostgreSQL:</strong> 14+ with SSL support</li>
                <li><i class="fab fa-redis text-blue-600 mr-2"></i><strong>Redis:</strong> 6.0+ for caching and sessions</li>
                <li><i class="fab fa-nginx text-blue-600 mr-2"></i><strong>Web Server:</strong> Nginx 1.20+ or Apache 2.4+</li>
                <li><i class="fas fa-shield-alt text-blue-600 mr-2"></i><strong>SSL:</strong> Let's Encrypt or commercial certificate</li>
            </ul>
        </div>
    </section>

    <!-- Docker Deployment -->
    <section id="docker" class="mb-12">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Docker Deployment</h2>
        
        <div class="space-y-6">
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Production Docker Compose</h3>
                <div class="code-block">
                    <pre><code>version: '3.8'

services:
  cas-app:
    image: cas-system:latest
    container_name: cas-app
    restart: unless-stopped
    environment:
      - APP_ENV=production
      - APP_DEBUG=false
      - APP_URL=https://cas.yourdomain.com
      - DB_CONNECTION=cas_system
      - PGHOST=cas-db
      - PGPORT=5432
      - PGDATABASE=cas_production
      - REDIS_HOST=cas-redis
      - REDIS_PORT=6379
    volumes:
      - ./storage:/var/www/html/storage
      - ./logs:/var/www/html/storage/logs
    networks:
      - cas-network
    depends_on:
      - cas-db
      - cas-redis

  cas-db:
    image: postgres:15-alpine
    container_name: cas-db
    restart: unless-stopped
    environment:
      - POSTGRES_DB=cas_production
      - POSTGRES_USER=cas_user
      - POSTGRES_PASSWORD=${DB_PASSWORD}
    volumes:
      - cas_db_data:/var/lib/postgresql/data
      - ./database/init:/docker-entrypoint-initdb.d
    networks:
      - cas-network
    command: postgres -c ssl=on -c ssl_cert_file=/etc/ssl/certs/server.crt

  cas-redis:
    image: redis:7-alpine
    container_name: cas-redis
    restart: unless-stopped
    command: redis-server --requirepass ${REDIS_PASSWORD}
    volumes:
      - cas_redis_data:/data
    networks:
      - cas-network

  cas-nginx:
    image: nginx:alpine
    container_name: cas-nginx
    restart: unless-stopped
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./nginx/production.conf:/etc/nginx/nginx.conf
      - ./ssl:/etc/nginx/ssl
      - cas_logs:/var/log/nginx
    networks:
      - cas-network
    depends_on:
      - cas-app

volumes:
  cas_db_data:
  cas_redis_data:
  cas_logs:

networks:
  cas-network:
    driver: bridge</code></pre>
                </div>
            </div>

            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Build Production Image</h3>
                <div class="code-block">
                    <pre><code># Build production image
docker build -t cas-system:latest -f docker/production/Dockerfile .

# Create production environment file
cp .env.production.example .env.production

# Edit production environment variables
nano .env.production

# Deploy with production compose
docker-compose -f docker-compose.production.yml up -d</code></pre>
                </div>
            </div>
        </div>
    </section>

    <!-- Kubernetes Deployment -->
    <section id="kubernetes" class="mb-12">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Kubernetes Deployment</h2>
        
        <div class="space-y-6">
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Namespace and ConfigMap</h3>
                <div class="code-block">
                    <pre><code>apiVersion: v1
kind: Namespace
metadata:
  name: cas-system
---
apiVersion: v1
kind: ConfigMap
metadata:
  name: cas-config
  namespace: cas-system
data:
  APP_ENV: "production"
  APP_DEBUG: "false"
  APP_URL: "https://cas.yourdomain.com"
  DB_CONNECTION: "cas_system"
  PGHOST=cas-postgresql"
  PGPORT: "5432"
  PGDATABASE: "cas_production"
  REDIS_HOST: "cas-redis"
  REDIS_PORT: "6379"</code></pre>
                </div>
            </div>

            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Application Deployment</h3>
                <div class="code-block">
                    <pre><code>apiVersion: apps/v1
kind: Deployment
metadata:
  name: cas-app
  namespace: cas-system
spec:
  replicas: 3
  selector:
    matchLabels:
      app: cas-app
  template:
    metadata:
      labels:
        app: cas-app
    spec:
      containers:
      - name: cas-app
        image: cas-system:latest
        ports:
        - containerPort: 8000
        envFrom:
        - configMapRef:
            name: cas-config
        - secretRef:
            name: cas-secrets
        resources:
          requests:
            memory: "512Mi"
            cpu: "250m"
          limits:
            memory: "1Gi"
            cpu: "500m"
        readinessProbe:
          httpGet:
            path: /health
            port: 8000
          initialDelaySeconds: 30
          periodSeconds: 10
        livenessProbe:
          httpGet:
            path: /health
            port: 8000
          initialDelaySeconds: 60
          periodSeconds: 30
---
apiVersion: v1
kind: Service
metadata:
  name: cas-service
  namespace: cas-system
spec:
  selector:
    app: cas-app
  ports:
    - protocol: TCP
      port: 80
      targetPort: 8000
  type: ClusterIP
---
apiVersion: networking.k8s.io/v1
kind: Ingress
metadata:
  name: cas-ingress
  namespace: cas-system
  annotations:
    nginx.ingress.kubernetes.io/ssl-redirect: "true"
    cert-manager.io/cluster-issuer: "letsencrypt-prod"
spec:
  ingressClassName: nginx
  tls:
  - hosts:
    - auth.example.com
    secretName: cas-tls-secret
  rules:
  - host: auth.example.com
    http:
      paths:
      - path: /
        pathType: Prefix
        backend:
          service:
            name: cas-service
            port:
              number: 80</code></pre>
                </div>
            </div>
        </div>
    </section>

    <!-- Environment Configuration -->
    <section id="environment" class="mb-12">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Environment Configuration</h2>
        
        <div class="space-y-6">
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
                <div class="flex items-start">
                    <i class="fas fa-exclamation-triangle text-yellow-600 mr-3 mt-1"></i>
                    <div>
                        <h3 class="text-lg font-semibold text-yellow-800 mb-2">Critical Security Settings</h3>
                        <p class="text-yellow-700">These environment variables must be configured for production deployment:</p>
                    </div>
                </div>
            </div>

            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Required Environment Variables</h3>
                <div class="code-block">
                    <pre><code># Application Configuration
APP_NAME="CAS Authentication System"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://cas.yourdomain.com

# Database Configuration
DB_CONNECTION=cas_system
PGHOST=your-postgres-host
PGPORT=5432
PGDATABASE=cas_production
PGUSER=cas_user
PGPASSWORD=secure-database-password

# JWT Secrets (Generate with: openssl rand -base64 32)
JWT_SECRET=your-jwt-secret-key-32-chars-minimum
CUSTOMER_PORTAL_JWT_SECRET=your-portal-jwt-secret

# Session Security
SESSION_DRIVER=redis
SESSION_LIFETIME=120
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true

# Cache Configuration
CACHE_DRIVER=redis
REDIS_HOST=your-redis-host
REDIS_PASSWORD=secure-redis-password
REDIS_PORT=6379

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-smtp-username
MAIL_PASSWORD=your-smtp-password
MAIL_ENCRYPTION=tls

# reCAPTCHA Configuration
RECAPTCHAV3_SITEKEY=your-recaptcha-site-key
RECAPTCHAV3_SECRET=your-recaptcha-secret-key</code></pre>
                </div>
            </div>
        </div>
    </section>

    <!-- Production Security -->
    <section id="security" class="mb-12">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Production Security</h2>
        
        <div class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-green-50 border border-green-200 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-green-900 mb-4">
                        <i class="fas fa-shield-alt mr-2"></i>SSL/TLS Configuration
                    </h3>
                    <ul class="space-y-2 text-green-800">
                        <li><i class="fas fa-check mr-2"></i>Force HTTPS redirects</li>
                        <li><i class="fas fa-check mr-2"></i>TLS 1.3 minimum</li>
                        <li><i class="fas fa-check mr-2"></i>HSTS headers</li>
                        <li><i class="fas fa-check mr-2"></i>Certificate auto-renewal</li>
                    </ul>
                </div>

                <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-blue-900 mb-4">
                        <i class="fas fa-database mr-2"></i>Database Security
                    </h3>
                    <ul class="space-y-2 text-blue-800">
                        <li><i class="fas fa-check mr-2"></i>SSL connections only</li>
                        <li><i class="fas fa-check mr-2"></i>Role-based access</li>
                        <li><i class="fas fa-check mr-2"></i>Encrypted backups</li>
                        <li><i class="fas fa-check mr-2"></i>Connection pooling</li>
                    </ul>
                </div>
            </div>

            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Security Checklist</h3>
                <div class="space-y-3">
                    <div class="flex items-center">
                        <input type="checkbox" class="mr-3" disabled>
                        <span class="text-gray-700">Change all default passwords and secrets</span>
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" class="mr-3" disabled>
                        <span class="text-gray-700">Configure firewall rules (ports 80, 443 only)</span>
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" class="mr-3" disabled>
                        <span class="text-gray-700">Enable fail2ban for brute force protection</span>
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" class="mr-3" disabled>
                        <span class="text-gray-700">Set up SSL certificate auto-renewal</span>
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" class="mr-3" disabled>
                        <span class="text-gray-700">Configure backup and monitoring</span>
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" class="mr-3" disabled>
                        <span class="text-gray-700">Review and configure IP whitelist</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Monitoring & Maintenance -->
    <section id="monitoring" class="mb-12">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Monitoring & Maintenance</h2>
        
        <div class="space-y-6">
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Health Check Endpoints</h3>
                <div class="code-block">
                    <pre><code># Application health check
GET /health

# Database connectivity check
GET /health/database

# Redis connectivity check  
GET /health/redis

# Full system status
GET /health/full</code></pre>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-chart-line mr-2"></i>Key Metrics to Monitor
                    </h3>
                    <ul class="space-y-2 text-gray-700">
                        <li><i class="fas fa-dot-circle text-blue-600 mr-2"></i>Response time (&lt; 200ms)</li>
                        <li><i class="fas fa-dot-circle text-blue-600 mr-2"></i>Error rate (&lt; 1%)</li>
                        <li><i class="fas fa-dot-circle text-blue-600 mr-2"></i>Authentication success rate</li>
                        <li><i class="fas fa-dot-circle text-blue-600 mr-2"></i>Database connection pool</li>
                        <li><i class="fas fa-dot-circle text-blue-600 mr-2"></i>Memory and CPU usage</li>
                    </ul>
                </div>

                <div class="bg-gray-50 border border-gray-200 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-tools mr-2"></i>Maintenance Tasks
                    </h3>
                    <ul class="space-y-2 text-gray-700">
                        <li><i class="fas fa-dot-circle text-green-600 mr-2"></i>Daily: Log review and cleanup</li>
                        <li><i class="fas fa-dot-circle text-green-600 mr-2"></i>Weekly: Database backup verification</li>
                        <li><i class="fas fa-dot-circle text-green-600 mr-2"></i>Monthly: Security patch updates</li>
                        <li><i class="fas fa-dot-circle text-green-600 mr-2"></i>Quarterly: SSL certificate renewal</li>
                        <li><i class="fas fa-dot-circle text-green-600 mr-2"></i>Yearly: Security audit</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Support and Troubleshooting -->
    <div class="bg-gray-50 border border-gray-200 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">
            <i class="fas fa-life-ring mr-2"></i>Support and Troubleshooting
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h4 class="font-semibold text-gray-800 mb-2">Common Issues</h4>
                <ul class="space-y-1 text-sm text-gray-600">
                    <li><a href="#" class="hover:text-blue-600">Database connection errors</a></li>
                    <li><a href="#" class="hover:text-blue-600">SSL certificate issues</a></li>
                    <li><a href="#" class="hover:text-blue-600">Performance optimization</a></li>
                    <li><a href="#" class="hover:text-blue-600">Authentication failures</a></li>
                </ul>
            </div>
            <div>
                <h4 class="font-semibold text-gray-800 mb-2">Log Locations</h4>
                <ul class="space-y-1 text-sm text-gray-600 font-mono">
                    <li>Application: <code>/var/www/html/storage/logs/</code></li>
                    <li>Nginx: <code>/var/log/nginx/</code></li>
                    <li>PostgreSQL: <code>/var/log/postgresql/</code></li>
                    <li>System: <code>/var/log/syslog</code></li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection