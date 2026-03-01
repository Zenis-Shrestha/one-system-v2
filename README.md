One System - Docker Deployment Guide

## Overview

This guide provides complete instructions for deploying the One System (Central Authentication Service) server using Docker containers. The deployment includes a production-ready setup with PostgreSQL, Redis, Nginx load balancer, queue workers, and scheduler services.

## Architecture

The Docker deployment consists of the following services:

- **One System Application** (PHP 8.3-FPM + Nginx)
- **PostgreSQL 16** (Primary database)
- **Redis 7** (Caching and session storage)
- **Nginx Load Balancer** (Optional, for SSL termination)
- **Queue Worker** (Background job processing)
- **Scheduler** (Laravel task scheduling)

## Prerequisites

- Docker Engine 20.10+
- Docker Compose 2.0+
- At least 4GB RAM available
- Ports 80, 443, 5432, 6379, 8000 available

## Quick Start

### 1. Clone and Prepare

```bash
git clone git@github.com:InSol-2021/one-system.git
cd one-system
```

### 2. Environment Setup

Create your environment file:

```bash
# Copy the Docker environment template
cp .env.docker .env

# Or create from example
cp .env.example .env
```

### 3. Deploy with Automated Script

```bash
# Make deployment script executable
chmod +x deploy.sh

# Deploy the system
./deploy.sh deploy
```

### 4. Verify Deployment

```bash
# Check service status
./deploy.sh status

# View application logs
./deploy.sh logs

# Test health endpoint
curl http://localhost:8000/health
```

## Manual Deployment Steps

### Step 1: Environment Configuration

Create and configure the `.env` file:

```bash
APP_NAME="One System"
APP_ENV=production
APP_KEY=base64:CHANGEME
APP_DEBUG=false
APP_URL=http://localhost:8000

DB_CONNECTION=cas_system
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=cas_system
DB_USERNAME=cas_user
DB_PASSWORD=cas_secure_password

CACHE_DRIVER=redis
SESSION_DRIVER=database
QUEUE_CONNECTION=redis

REDIS_HOST=redis
REDIS_PORT=6379
REDIS_PASSWORD=cas_redis_password

JWT_SECRET=your-super-secure-jwt-secret-key-change-this
SIGNATURE_SECRET=your-super-secure-signature-secret-change-this
TOKEN_EXPIRY_MINUTES=60
ENABLE_AUDIT_LOGGING=true
ENABLE_IP_WHITELIST=true

ENABLE_2FA=true
PASSWORD_MIN_LENGTH=8
ENABLE_CAPTCHA=true
RECAPTCHA_SITE_KEY=your-recaptcha-site-key
RECAPTCHA_SECRET_KEY=your-recaptcha-secret-key
```

### Step 2: Build and Deploy

```bash
# Stop any existing containers
docker-compose down --remove-orphans

# Build fresh images
docker-compose build --no-cache

# Start all services
docker-compose up -d

# Check service status
docker-compose ps
```

### Step 3: Database Setup

Wait for PostgreSQL to be ready, then run migrations:

```bash
# Wait for database to be ready
docker-compose exec postgres pg_isready -U cas_user -d cas_system

# Run database migrations
docker-compose exec one-system php artisan migrate --force

# Seed the database
docker-compose exec one-system php artisan db:seed --force
```

## Essential Artisan Commands

### Database Management

```bash
# Database migrations
docker-compose exec one-system php artisan migrate --force
docker-compose exec one-system php artisan migrate:rollback
docker-compose exec one-system php artisan migrate:refresh --seed --force

# Database seeding
docker-compose exec one-system php artisan db:seed --force
docker-compose exec one-system php artisan db:seed --class=AdminUserSeeder --force

# Schema management
docker-compose exec one-system php artisan db:push --force
docker-compose exec one-system php artisan schema:dump
```

### CAS Management Commands

```bash
# User Management
docker-compose exec one-system php artisan cas:check-user admin
docker-compose exec one-system php artisan cas:unlock-account admin
docker-compose exec one-system php artisan cas:list-users --active

# System Health and Monitoring
docker-compose exec one-system php artisan cas:health-check
docker-compose exec one-system php artisan cas:test-db
docker-compose exec one-system php artisan cas:system-metrics

# Security Operations
docker-compose exec one-system php artisan cas:validate-jwt "your-jwt-token"
docker-compose exec one-system php artisan cas:test-credentials 1
docker-compose exec one-system php artisan cas:regenerate-secrets --confirm

# Cache and Performance
docker-compose exec one-system php artisan cache:clear
docker-compose exec one-system php artisan config:cache
docker-compose exec one-system php artisan route:cache
docker-compose exec one-system php artisan view:cache
docker-compose exec one-system php artisan optimize

# Queue Management
docker-compose exec one-system php artisan queue:work --daemon
docker-compose exec one-system php artisan queue:restart
docker-compose exec one-system php artisan queue:failed
docker-compose exec one-system php artisan queue:retry all
```

### Maintenance Commands

```bash
# Clear all caches
docker-compose exec one-system php artisan optimize:clear

# Generate application key
docker-compose exec one-system php artisan key:generate --force

# Storage linking
docker-compose exec one-system php artisan storage:link

# Database optimization
docker-compose exec one-system php artisan cas:optimize-db
docker-compose exec one-system php artisan cas:rebuild-indexes
```

## Production Configuration

### SSL Configuration

For production with SSL, create SSL certificates:

```bash
# Create SSL directory
mkdir -p docker/nginx/ssl

# Generate self-signed certificate (for testing)
openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
  -keyout docker/nginx/ssl/cas-server.key \
  -out docker/nginx/ssl/cas-server.crt \
  -subj "/C=US/ST=State/L=City/O=Organization/CN=your-domain.com"

# Deploy with load balancer
docker-compose --profile with-lb up -d
```

### Environment Variables for Production

```bash
# Security (generate secure values)
APP_KEY=$(docker-compose exec one-system php artisan key:generate --show)
JWT_SECRET=$(openssl rand -hex 32)
SIGNATURE_SECRET=$(openssl rand -hex 32)
REDIS_PASSWORD=$(openssl rand -hex 16)
DB_PASSWORD=$(openssl rand -hex 16)

# Production settings
APP_DEBUG=false
APP_ENV=production
LOG_LEVEL=error
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=strict
```

## Service Management

### Start/Stop Services

```bash
# Start all services
docker-compose up -d

# Start with queue worker and scheduler
docker-compose --profile with-scheduler up -d

# Start with nginx load balancer
docker-compose --profile with-lb up -d

# Stop all services
docker-compose down

# Stop and remove volumes
docker-compose down -v
```

### Service Health Checks

```bash
# Check all services
docker-compose ps

# Check individual service logs
docker-compose logs one-system
docker-compose logs postgres
docker-compose logs redis

# Follow logs in real-time
docker-compose logs -f one-system

# Health check endpoints
curl http://localhost:8000/health
curl http://localhost:8000/api/health
```

### Backup and Restore

```bash
# Database backup
docker-compose exec postgres pg_dump -U cas_user cas_system > backup.sql

# Database restore
docker-compose exec -T postgres psql -U cas_user cas_system < backup.sql

# Volume backup
docker run --rm -v laravel-cas_postgres_data:/data -v $(pwd):/backup alpine tar czf /backup/postgres-backup.tar.gz -C /data .

# Volume restore
docker run --rm -v laravel-cas_postgres_data:/data -v $(pwd):/backup alpine tar xzf /backup/postgres-backup.tar.gz -C /data
```

## Monitoring and Troubleshooting

### Performance Monitoring

```bash
# System metrics
docker-compose exec one-system php artisan cas:system-metrics

# Database performance
docker-compose exec one-system php artisan cas:monitor-queries

# Slow query analysis
docker-compose exec postgres psql -U cas_user -d cas_system -c "
SELECT query, mean_time, calls 
FROM pg_stat_statements 
ORDER BY mean_time DESC 
LIMIT 10;"
```

### Common Issues and Solutions

#### Database Connection Issues

```bash
# Check PostgreSQL status
docker-compose exec postgres pg_isready -U cas_user -d cas_system

# Restart database service
docker-compose restart postgres

# Check database logs
docker-compose logs postgres
```

#### Redis Connection Issues

```bash
# Test Redis connection
docker-compose exec redis redis-cli ping

# Clear Redis cache
docker-compose exec redis redis-cli FLUSHALL

# Restart Redis service
docker-compose restart redis
```

#### Application Issues

```bash
# Clear all Laravel caches
docker-compose exec one-system php artisan optimize:clear

# Regenerate autoloader
docker-compose exec one-system composer dump-autoload

# Check application logs
docker-compose logs one-system
tail -f storage/logs/laravel.log
```

#### Queue Worker Issues

```bash
# Restart queue workers
docker-compose exec one-system php artisan queue:restart

# Check failed jobs
docker-compose exec one-system php artisan queue:failed

# Retry failed jobs
docker-compose exec one-system php artisan queue:retry all
```

## Security Best Practices

### Production Security Checklist

- [ ] Change all default passwords
- [ ] Generate secure APP_KEY, JWT_SECRET, SIGNATURE_SECRET
- [ ] Enable SSL/TLS certificates
- [ ] Configure firewall rules
- [ ] Set up regular database backups
- [ ] Enable audit logging
- [ ] Configure IP whitelisting
- [ ] Set up monitoring and alerting
- [ ] Regular security updates

### Secrets Management

```bash
# Generate secure secrets
docker-compose exec one-system php artisan cas:regenerate-secrets --confirm

# Validate system security
docker-compose exec one-system php artisan cas:security-audit

# Test HMAC signature validation
docker-compose exec one-system php artisan cas:validate-signature "test-payload" "signature"
```

## Scaling and High Availability

### Horizontal Scaling

```bash
# Scale application instances
docker-compose up -d --scale one-system=3

# Scale queue workers
docker-compose up -d --scale queue-worker=5

# Load balancer configuration
docker-compose --profile with-lb up -d
```

### Database Clustering

For high availability, consider PostgreSQL clustering:

```yaml
# Add to docker-compose.yml for master-slave setup
postgres-slave:
  image: postgres:16-alpine
  environment:
    PGUSER: cas_user
    POSTGRES_PASSWORD: cas_secure_password
    POSTGRES_MASTER_SERVICE: postgres
  command: |
    bash -c "
    until pg_basebackup -h postgres -D /var/lib/postgresql/data -U cas_user -v -P -W
    do
      echo 'Waiting for master to connect...'
      sleep 1s
    done
    echo 'Backup done, starting replica...'
    echo 'standby_mode = on' >> /var/lib/postgresql/data/recovery.conf
    echo 'primary_conninfo = host=postgres port=5432 user=cas_user' >> /var/lib/postgresql/data/recovery.conf
    postgres
    "
```

## Deployment Automation

### CI/CD Integration

```yaml
# GitHub Actions example (.github/workflows/deploy.yml)
name: Deploy Laravel CAS Server
on:
  push:
    branches: [main]

jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
    
      - name: Deploy to server
        run: |
          ssh ${{ secrets.SERVER_USER }}@${{ secrets.SERVER_HOST }} "
            cd /path/to/laravel-cas &&
            git pull origin main &&
            ./deploy.sh deploy
          "
```

### Health Check Monitoring

```bash
# Setup monitoring script
cat > monitor.sh << 'EOF'
#!/bin/bash
while true; do
  if ! curl -f http://localhost:8000/health > /dev/null 2>&1; then
    echo "$(date): Health check failed, restarting services..."
    docker-compose restart one-system
  fi
  sleep 30
done
EOF

chmod +x monitor.sh
nohup ./monitor.sh &
```

## Conclusion

This Docker deployment provides a robust, scalable, and production-ready environment for the Laravel CAS Server. The automated deployment script simplifies the process, while the manual commands provide fine-grained control for advanced configurations.

For additional support and advanced configurations, refer to the Laravel documentation and Docker best practices.

---

**Quick Reference Commands:**

```bash
# Deploy system
./deploy.sh deploy

# Check status
./deploy.sh status

# View logs
./deploy.sh logs

# Stop system
./deploy.sh stop

# Clean up
./deploy.sh clean
```
