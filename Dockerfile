FROM php:8.2-apache

RUN a2dismod mpm_event mpm_worker && \
    a2enmod mpm_prefork rewrite && \
    docker-php-ext-install mysqli

COPY . /var/www/html/

RUN sed -i 's|AllowOverride None|AllowOverride All|g' /etc/apache2/apache2.conf

EXPOSE 80
