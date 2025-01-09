FROM php:8.2-fpm
WORKDIR /var/www/html
COPY ./space-backend/composer.json ./space-backend/composer.lock ./
RUN apt-get update && apt-get install -y \
    git \
    libzip-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo pdo_mysql zip \
    && pecl install redis \
    && docker-php-ext-enable redis
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer install --no-interaction --no-scripts --no-autoloader
COPY ./space-backend .
COPY ./space-backend/init-container.sh /usr/local/bin/init-container.sh
RUN composer dump-autoload --optimize
ENV PHP_MEMORY_LIMIT=256M
ENV PHP_MAX_EXECUTION_TIME=30
EXPOSE 9000
RUN chmod u+w .env
RUN chmod +x /usr/local/bin/init-container.sh
CMD ["/usr/local/bin/init-container.sh"]

# RUN bash ./init-container.sh
# RUN chmod 644 .env
# CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=9000"]
