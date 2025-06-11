FROM laravelsail/php84-composer:latest

# Install php with extensions
RUN apt-get update && apt-get install -y \
    unzip git curl libzip-dev libpng-dev libpq-dev libonig-dev libxml2-dev \
    && docker-php-ext-install zip pdo pdo_pgsql \
    && apt-get clean

# Slect workdir in docker
WORKDIR /var/www/html

# Copy proyect from WORKDIR
COPY . .

# Install production dependences and optimize app
RUN composer install --no-dev --optimize-autoloader

# Clean config and genereta key for work
RUN php artisan config:clear && php artisan key:generate

# Open work port for render
EXPOSE 8080

# Run migrations and seedes and start server
CMD php artisan migrate --force && php artisan db:seed --force && php artisan serve --host=0.0.0.0 --port=8080
