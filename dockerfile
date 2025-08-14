FROM php:8.2-apache

# Install ekstensi untuk MySQL
RUN docker-php-ext-install mysqli pdo pdo_mysql

# (opsional) aktifkan modul Apache yang sering dipakai
RUN a2enmod rewrite headers

# (opsional) set timezone agar log PHP rapi
RUN echo "date.timezone=Asia/Makassar" > /usr/local/etc/php/conf.d/timezone.ini
