FROM php:8.2-apache

# Tools kecil untuk healthcheck (opsional)
RUN apt-get update && apt-get install -y --no-install-recommends curl \
    && rm -rf /var/lib/apt/lists/*

# Ekstensi MySQL
RUN docker-php-ext-install mysqli pdo pdo_mysql

# (opsional) OPcache untuk performa
RUN docker-php-ext-install opcache \
 && docker-php-ext-enable opcache

# Modul Apache yg sering dipakai
RUN a2enmod rewrite headers

# Timezone PHP
RUN echo "date.timezone=Asia/Makassar" > /usr/local/etc/php/conf.d/timezone.ini
