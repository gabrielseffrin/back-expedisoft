#!/bin/bash

echo "Starting deployment scripts..."

# Otimizar as configurações do Laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Rodar migrações do banco de dados (ignorando erros caso já existam)
php artisan migrate --force

echo "Deployment scripts finished. Starting Supervisor..."

# Iniciar o Supervisor (Nginx, PHP-FPM e Queue Worker)
/usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
