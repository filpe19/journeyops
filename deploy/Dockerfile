# PHP-FPM runtime for the public JourneyOps demo.
# The official image already ships pdo_sqlite, mbstring, dom and friends;
# the host keeps its own PHP untouched.
FROM php:8.4-fpm-bookworm

RUN docker-php-ext-enable opcache

COPY php.ini /usr/local/etc/php/conf.d/zz-journeyops.ini
COPY fpm-pool.conf /usr/local/etc/php-fpm.d/zz-journeyops.conf

WORKDIR /var/www/journeyops
