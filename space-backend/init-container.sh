#!/bin/bash

echo "" >> .env
echo "SPACE_DB_HOST=$SPACE_DB_HOST" >> .env
echo "SPACE_DB_PORT=$SPACE_DB_PORT" >> .env
echo "SPACE_DB_DATABASE=$SPACE_DB_DATABASE" >> .env
echo "SPACE_DB_USERNAME=$SPACE_DB_USERNAME" >> .env
echo "SPACE_DB_PASSWORD=$SPACE_DB_PASSWORD" >> .env
echo "CACHE_STORE=redis" >> .env
echo "CACHE_PREFIX=$REDIS_CACHE_PREFIX" >> .env
echo "REDIS_CLIENT=$REDIS_CLIENT" >> .env
echo "REDIS_HOST=$REDIS_HOST" >> .env
echo "REDIS_PORT=$REDIS_PORT" >> .env
echo "REDIS_USERNAME=$REDIS_USERNAME" >> .env
echo "REDIS_PASSWORD=$REDIS_PASSWORD" >> .env
echo "FRETE_MELHORENVIO_API_URL=$FRETE_MELHORENVIO_API_URL" >> .env
echo "FRETE_MELHORENVIO_API_TOKEN=$FRETE_MELHORENVIO_API_TOKEN" >> .env
echo "FRETE_LALAMOVE_KEY=$FRETE_LALAMOVE_KEY" >> .env
echo "FRETE_LALAMOVE_SECRET=$FRETE_LALAMOVE_SECRET" >> .env
echo "TINY_TOKEN=$TINY_TOKEN" >> .env

echo "[arte arena security] Configurando variáveis de ambiente..."

php artisan cache:clear && \
php artisan config:clear && \
php artisan route:clear && \
php artisan view:clear && \
php artisan serve --host=0.0.0.0 --port=9000 & 
tail -f /dev/null
