FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    libpq-dev \
    libzip-dev

RUN docker-php-ext-install pdo pdo_pgsql zip

WORKDIR /var/www

COPY . .

RUN curl -sS https://getcomposer.org/installer | php \
    && mv composer.phar /usr/local/bin/composer

RUN composer install

CMD ["php-fpm"]
7. Create Frontend Dockerfile (Vue 3)

Location

frontend/Dockerfile
FROM node:20

WORKDIR /app

COPY . .

RUN npm install
RUN npm run build

CMD ["npm", "run", "preview"]