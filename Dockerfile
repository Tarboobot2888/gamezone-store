FROM php:8.2-fpm
WORKDIR /var/www/html
COPY . .
RUN apt-get update && apt-get install -y git unzip libpng-dev libonig-dev libxml2-dev zip curl && \
    docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer install --ignore-platform-reqs --no-dev --optimize-autoloader
RUN npm install && npm run build
 yg374e-codex/refactor-and-productionize-gamezonestore
EXPOSE 8000
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]

CMD ["php-fpm"]
 main
