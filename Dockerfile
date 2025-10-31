FROM php:8.4-apache

RUN docker-php-ext-install mysqli

COPY http_API/ /var/www/html/http_API/

RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
