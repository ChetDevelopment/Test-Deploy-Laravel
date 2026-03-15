# ===========================================
# Full-Stack Deployment: Vue 3 + Laravel
# ===========================================

# ===========================================
# Stage 1: Build Frontend (Node.js)
# ===========================================
FROM node:20-alpine AS frontend-builder

# Set working directory
WORKDIR /app/frontend

# Copy frontend files
COPY frontend/package*.json ./
COPY frontend/vite.config.js ./
COPY frontend/tailwind.config.js ./
COPY frontend/postcss.config.js ./
COPY frontend/tsconfig.json ./
COPY frontend/index.html ./
COPY frontend/src ./src
COPY frontend/public ./public

# Install dependencies
RUN npm ci

# Build frontend
RUN npm run build

# ===========================================
# Stage 2: Build Backend (PHP)
# ===========================================
FROM php:8.2-fpm-alpine AS backend-builder

# Install system dependencies
RUN apk add --no-cache \
    curl \
    git \
    unzip \
    libzip-dev \
    zip \
    nginx \
    supervisor \
    nodejs \
    npm

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql pdo_sqlite zip pcntl exif gd

# Install Redis extension
RUN pecl install redis-6.0.2 && docker-php-ext-enable redis

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app/backend

# Copy backend files (excluding vendor - will be installed)
COPY backend/composer.json ./
COPY backend/.env.production.example ./.env
COPY backend/bootstrap/ ./bootstrap/
COPY backend/config/ ./config/
COPY backend/app/ ./app/
COPY backend/database/ ./database/
COPY backend/routes/ ./routes/
COPY backend/public/ ./public/
COPY backend/resources/ ./resources/
COPY backend/storage/ ./storage/
COPY backend/artisan ./

# Copy package.json for potential npm builds in backend
COPY backend/package.json ./
COPY backend/vite.config.js ./

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --prefer-dist --no-interaction

# Generate keys
RUN php artisan key:generate --force
RUN php artisan jwt:secret --force --no-interaction

# Clear caches
RUN php artisan config:clear
RUN php artisan route:clear
RUN php artisan view:clear

# ===========================================
# Stage 3: Production Image
# ===========================================
FROM php:8.2-fpm-alpine AS production

# Install system dependencies
RUN apk add --no-cache \
    curl \
    git \
    nginx \
    supervisor \
    tzdata

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql pdo_sqlite zip pcntl exif gd

# Install Redis extension
RUN pecl install redis-6.0.2 && docker-php-ext-enable redis

# Set timezone
ENV TZ=Asia/Bangkok
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# Create application directory
RUN mkdir -p /app
WORKDIR /app

# Copy built frontend from stage 1
COPY --from=frontend-builder /app/frontend/dist /app/public

# Copy built backend from stage 2
COPY --from=backend-builder /app/backend /app/backend

# Set permissions
RUN chown -R nginx:nginx /app/backend/storage /app/backend/bootstrap/cache
RUN chmod -R 755 /app/backend/storage /app/backend/bootstrap/cache
RUN chmod +x /app/backend/artisan

# Switch to non-root user
RUN adduser -D -u 1000 nginx

# ===========================================
# Start Services
# ===========================================
EXPOSE 8080

# Start supervisor (manages php-fpm and nginx)
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
