FROM php:8.3-fpm-alpine

# تثبيت الحزم الأساسية وإضافات PHP المطلوبة
RUN apk add --no-cache \
    linux-headers \
    build-base \
    zlib-dev \
    libxml2-dev \
    libzip-dev \
    postgresql-dev \
    unzip \
    curl \
    git \
    oniguruma-dev

# تنصيب وتفعيل إضافات PHP (بما فيها pdo_pgsql للتواصل مع PostgreSQL)
RUN docker-php-ext-install \
    pdo_pgsql \
    pgsql \
    mbstring \
    xml \
    zip \
    pcntl \
    bcmath

# تنصيب إضافة Redis
RUN pecl install redis && docker-php-ext-enable redis

# تنصيب Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# تحديد مسار العمل
WORKDIR /var/www/html

# سنستخدم أمر serve هنا لتبسيط البيئة والاستغناء عن Nginx كما هو مطلوب (4 حاويات فقط)
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
