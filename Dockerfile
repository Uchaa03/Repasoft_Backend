FROM laravelsail/php84-composer:latest

# Install required dependencies
RUN apt-get update && apt-get install -y \
    unzip git curl libzip-dev libpng-dev libpq-dev libonig-dev libxml2-dev \
    && docker-php-ext-install zip pdo pdo_pgsql \
    && apt-get autoremove -y \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Set working directory
WORKDIR /var/www/html

# Copy project files
COPY . .

# Install production dependencies and optimize autoloader
RUN composer install --no-dev --optimize-autoloader

# Clear config and cache routes
RUN php artisan config:clear && \
    php artisan config:cache && \
    php artisan route:cache

# Create app key file only if it does not exist
RUN if [ ! -f .env ]; then \
        cp .env.example .env; \
        php artisan key:generate; \
    fi

# Expose port (for reference, Render will use the PORT variable)
EXPOSE 8080

# Custom startup command for Render
CMD php artisan migrate --force && \
    php artisan db:seed --force && \
    php artisan serve --host=0.0.0.0 --port=${PORT:-8080}

