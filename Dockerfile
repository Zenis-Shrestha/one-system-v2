FROM php:8.3-fpm-alpine

WORKDIR /var/www/html

RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    oniguruma-dev \
    postgresql-dev \
    nginx \
    supervisor \
    bash \
    nodejs \
    npm \
    openssl

RUN docker-php-ext-install \
    pdo_pgsql \
    pgsql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    xml \
    soap

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN addgroup -g 1000 www && \
    adduser -u 1000 -G www -s /bin/sh -D www

COPY --chown=www:www . .

RUN composer install --no-dev --optimize-autoloader --no-interaction

RUN npm install && npm run build

RUN chown -R www:www /var/www/html && \
    chmod -R 755 /var/www/html/storage && \
    chmod -R 755 /var/www/html/bootstrap/cache

COPY docker/nginx/nginx.conf /etc/nginx/nginx.conf
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf

COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 80

USER www

HEALTHCHECK --interval=30s --timeout=10s --start-period=5s --retries=3 \
    CMD curl -f http://localhost/health || exit 1

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
