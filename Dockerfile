FROM php:8.2-apache-bookworm

RUN apt-get update && apt-get install -y --no-install-recommends \
        libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
        libzip-dev libcurl4-openssl-dev libssl-dev \
        libgmp-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        gd gmp mysqli pdo_mysql zip bcmath curl opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2.2 /usr/bin/composer /usr/bin/composer

# 启用 mod_rewrite
RUN a2enmod rewrite

# Apache 配置：允许 .htaccess 并禁止访问敏感目录
COPY <<'APACHE' /etc/apache2/conf-available/easypay.conf
<Directory /var/www/html>
    AllowOverride All
    Require all granted
</Directory>
<DirectoryMatch "^/var/www/html/(plugins|includes|install)">
    Require all denied
</DirectoryMatch>
<Files "config.php">
    Require all denied
</Files>
APACHE
RUN a2enconf easypay

# PHP 配置
RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
COPY <<'PHPINI' /usr/local/etc/php/conf.d/easypay.ini
upload_max_filesize = 20M
post_max_size = 20M
max_execution_time = 300
memory_limit = 256M
date.timezone = Asia/Shanghai
PHPINI

WORKDIR /var/www/html
COPY . .

# 安装 Composer 依赖
RUN COMPOSER_ALLOW_SUPERUSER=1 \
    php /usr/bin/composer install \
        --no-dev --optimize-autoloader --no-interaction \
        --working-dir=includes

RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
