FROM php:8.2-apache

RUN docker-php-ext-install mysqli

RUN a2enmod rewrite

COPY . /var/www/html/

RUN echo '<Directory /var/www/html>\nAllowOverride All\nRequire all granted\n</Directory>' >> /etc/apache2/conf-available/docker-php.conf

EXPOSE 80
