FROM php:8.4-fpm

RUN apt-get update && apt-get install -y \
    git unzip curl libicu-dev libpng-dev libjpeg-dev libfreetype6-dev libzip-dev libpq-dev libonig-dev && \
    docker-php-ext-install intl pdo pdo_mysql zip

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Verify Composer version
RUN composer --version

# Install Node.js 18 and npm
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - && \
    apt-get install -y nodejs && \
    node -v && npm -v

WORKDIR /var/www/html

COPY ./server/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["entrypoint.sh"]
CMD ["php-fpm"]
