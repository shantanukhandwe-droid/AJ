FROM php:8.2-apache

RUN docker-php-ext-install mysqli && a2enmod rewrite

COPY . /var/www/html/

RUN sed -i 's|AllowOverride None|AllowOverride All|g' /etc/apache2/apache2.conf

EXPOSE 80
