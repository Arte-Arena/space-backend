#!/bin/bash

echo "" >> .env
echo "SPACE_DB_HOST=$SPACE_DB_HOST" >> .env
echo "SPACE_DB_PORT=$SPACE_DB_PORT" >> .env
echo "SPACE_DB_DATABASE=$SPACE_DB_DATABASE" >> .env
echo "SPACE_DB_USERNAME=$SPACE_DB_USERNAME" >> .env
echo "SPACE_DB_PASSWORD=$SPACE_DB_PASSWORD" >> .env
echo "FRETE_MELHORENVIO_API_URL=$FRETE_MELHORENVIO_API_URL" >> .env
echo "FRETE_MELHORENVIO_API_TOKEN=$FRETE_MELHORENVIO_API_TOKEN" >> .env
echo "CACHE_STORE=redis" >> .env
echo "CACHE_PREFIX=$REDIS_CACHE_PREFIX" >> .env
echo "REDIS_CLIENT=phpredis" >> .env
echo "REDIS_HOST=$REDIS_PASSWORD" >> .env
echo "REDIS_PORT=$REDIS_PORT" >> .env
echo "REDIS_PASSWORD=$REDIS_PASSWORD" >> .env



echo "[arte arena security] Script de inicialização concluído com sucesso."

exec php artisan serve --host=0.0.0.0 --port=9000