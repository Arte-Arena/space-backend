FROM php:8.2-fpm
WORKDIR /var/www/html
COPY ./space-backend/composer.json ./space-backend/composer.lock ./
RUN apt-get update && apt-get install -y \
    git \
    libzip-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo pdo_mysql zip
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer install --no-interaction --no-scripts --no-autoloader
COPY ./space-backend .
RUN chmod u+w .env
ENTRYPOINT ["./init-container.sh"]
RUN chmod 644 .env
RUN composer dump-autoload --optimize
EXPOSE 9000
RUN cat .env
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=9000"]
