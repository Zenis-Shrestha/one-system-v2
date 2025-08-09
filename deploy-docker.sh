#!/bin/bash

set -e

PROJECT_NAME="one-system"
COMPOSE_FILE="docker-compose.yml"
ENV_FILE=".env"

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

sedi_command="sed -i"
if [[ "$OSTYPE" == "darwin"* ]]; then
  sedi_command="sed -i ''"
fi

log_info() {
    echo -e "${BLUE}ℹ️  $1${NC}"
}

log_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

log_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

log_error() {
    echo -e "${RED}❌ $1${NC}"
}

check_docker() {
    if ! command -v docker &> /dev/null; then
        log_error "Docker is not installed. Please install Docker first."
        exit 1
    fi

    if ! command -v docker-compose &> /dev/null; then
        log_error "Docker Compose is not installed. Please install Docker Compose first."
        exit 1
    fi

    log_success "Docker and Docker Compose are available"
}

prepare_environment() {
    log_info "Preparing environment..."

    if [ ! -f "$ENV_FILE" ]; then
        if [ -f ".env.example" ]; then
            cp .env.example "$ENV_FILE"
            log_warning "New .env file created from .env.example."
            log_error "Please edit the .env file with your secrets and run the script again."
            exit 1
        else
            log_error "No .env.example file found. Cannot prepare environment."
            exit 1
        fi
    fi

    if grep -q "APP_KEY=" "$ENV_FILE" && ! grep -q "APP_KEY=base64:" "$ENV_FILE"; then
        log_info "Generating new application key..."
        NEW_KEY=$(openssl rand -base64 32)
        $sedi_command "s|APP_KEY=|APP_KEY=base64:$NEW_KEY|g" "$ENV_FILE"
        log_success "Application key generated"
    fi

    if grep -q "your-super-secure-jwt-secret-key-change-this" "$ENV_FILE"; then
        log_info "Generating JWT secret..."
        JWT_SECRET=$(openssl rand -hex 32)
        $sedi_command "s|your-super-secure-jwt-secret-key-change-this|$JWT_SECRET|g" "$ENV_FILE"
        log_success "JWT secret generated"
    fi

    if grep -q "your-super-secure-signature-secret-change-this" "$ENV_FILE"; then
        log_info "Generating signature secret..."
        SIG_SECRET=$(openssl rand -hex 32)
        $sedi_command "s|your-super-secure-signature-secret-change-this|$SIG_SECRET|g" "$ENV_FILE"
        log_success "Signature secret generated"
    fi
}

build_frontend_assets() {
    log_info "Building frontend assets for production..."
    log_info "Installing NPM dependencies..."
    docker-compose run --rm node install
    log_info "Running npm run build..."
    docker-compose run --rm node run build
    log_success "Frontend assets built successfully."
}

deploy_services() {
    log_info "Building and starting Docker services..."

    docker-compose down --remove-orphans

    log_info "Building Docker images..."
    docker-compose build

    log_info "Starting services..."
    docker-compose up -d

    log_success "Services started successfully"
}

wait_for_services() {
    log_info "Waiting for services to be ready..."

    log_info "Waiting for Redis..."
    timeout=30
    while [ $timeout -gt 0 ]; do
        if [ "$(docker inspect -f '{{.State.Health.Status}}' one-system-redis)" = "healthy" ]; then
            log_success "Redis is ready"
            break
        fi
        sleep 2
        timeout=$((timeout-2))
    done

    if [ $timeout -le 0 ]; then
        log_error "Redis failed to start within timeout"
        exit 1
    fi

    log_info "Waiting for One System application..."
    timeout=120
    while [ $timeout -gt 0 ]; do
        if [ "$(docker inspect -f '{{.State.Health.Status}}' one-system-app)" = "healthy" ]; then
            log_success "One System application is ready"
            break
        fi
        sleep 3
        timeout=$((timeout-3))
    done

    if [ $timeout -le 0 ]; then
        log_error "One System failed to start within timeout"
        exit 1
    fi
}

run_migrations() {
    log_info "Running database migrations and seeding..."
    docker-compose run --rm artisan migrate --seed
    log_success "Database migrations and seeding completed."
}

show_status() {
    log_info "Deployment Status:"
    echo ""
    docker-compose ps
    echo ""

    log_info "Health Check (on http://localhost:96/health):"
    curl -s http://localhost:96/health || log_warning "Health check failed"
    echo ""
}

show_logs() {
    if [ "$1" = "logs" ]; then
        log_info "Showing application logs..."
        docker-compose logs -f app
    fi
}

update_code() {
#    log_info "Updating code from Git repository..."
#
#    if git pull origin master; then
#        log_success "Git pull completed successfully"
#    else
#        log_warning "Git pull failed or no changes to pull"
#    fi

    log_info "Restarting one system container..."
    docker compose restart

    # Wait for application to be ready
    log_info "Waiting for application to restart..."
    timeout=60
    while [ $timeout -gt 0 ]; do
        if curl -f http://localhost:8000/health &> /dev/null; then
            log_success "One System application is ready"
            break
        fi
        sleep 2
        timeout=$((timeout-2))
    done

    if [ $timeout -le 0 ]; then
        log_error "Application failed to restart within timeout"
        exit 1
    fi

    log_success "Code updated successfully!"
    show_status
}

main() {
    case "${1:-deploy}" in
        "deploy")
            check_docker
            prepare_environment
            build_frontend_assets
            deploy_services
            wait_for_services
            run_migrations
            show_status
            log_success "One System deployed successfully!"
            ;;
        "stop")
            log_info "Stopping services..."
            docker-compose down
            log_success "Services stopped"
            ;;
        "restart")
            log_info "Restarting services..."
            docker-compose restart
            log_success "Services restarted"
            ;;
        "update")
            update_code
            ;;
        "logs")
            show_logs "logs"
            ;;
        "status")
            show_status
            ;;
        "clean")
            log_warning "This will remove all containers, images, and volumes!"
            read -p "Are you sure? (y/N): " -n 1 -r
            echo
            if [[ $REPLY =~ ^[Yy]$ ]]; then
                docker-compose down -v --remove-orphans
                docker system prune -af
                log_success "Clean up completed"
            fi
            ;;
        *)
            echo "Usage: $0 {deploy|stop|restart|logs|status|clean}"
            echo ""
            echo "Commands:"
            echo "  deploy  - Deploy the One System"
            echo "  stop    - Stop all services"
            echo "  restart - Restart all services"
            echo "  logs    - Show application logs"
            echo "  status  - Show deployment status"
            echo "  clean   - Remove all containers and images"
            exit 1
            ;;
    esac
}

main "$@"