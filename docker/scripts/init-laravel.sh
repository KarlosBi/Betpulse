#!/bin/sh
# Instala Laravel limpio en la carpeta del proyecto (que ya contiene .git y docker/).
# composer create-project exige una carpeta vacía, así que se instala en /tmp y se copia.
set -e

cd /var/www/html

if [ -f artisan ]; then
    echo "Laravel ya está instalado (existe artisan). No se hace nada."
    exit 0
fi

echo ">> Descargando Laravel en /tmp/laravel ..."
rm -rf /tmp/laravel
composer create-project laravel/laravel /tmp/laravel --prefer-dist --no-interaction

echo ">> Copiando archivos al proyecto ..."
cp -a /tmp/laravel/. /var/www/html/
rm -rf /tmp/laravel

# El instalador crea una base SQLite por defecto; usamos PostgreSQL
rm -f database/database.sqlite

echo ">> Configurando .env y .env.example para PostgreSQL ..."
for f in .env .env.example; do
    sed -i \
        -e 's/^APP_NAME=.*/APP_NAME=BetPulse/' \
        -e 's|^APP_URL=.*|APP_URL=http://localhost:8080|' \
        -e 's/^DB_CONNECTION=.*/DB_CONNECTION=pgsql/' \
        -e 's/^#* *DB_HOST=.*/DB_HOST=db/' \
        -e 's/^#* *DB_PORT=.*/DB_PORT=5432/' \
        -e 's/^#* *DB_DATABASE=.*/DB_DATABASE=betpulse/' \
        -e 's/^#* *DB_USERNAME=.*/DB_USERNAME=betpulse/' \
        -e 's/^#* *DB_PASSWORD=.*/DB_PASSWORD=secret/' \
        "$f"
done

# Puertos publicados en el host (los usa docker-compose.yml)
grep -q '^APP_PORT=' .env || printf '\nAPP_PORT=8080\nFORWARD_DB_PORT=5433\n' >> .env

# Permisos de escritura para PHP-FPM (www-data)
chmod -R ug+rwX storage bootstrap/cache

echo ">> Listo. Laravel $(php artisan --version) instalado."
