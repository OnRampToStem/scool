FROM php:8.4.3-apache-bookworm

ENV COMPOSER_ALLOW_SUPERUSER=1

RUN apt-get update -y && apt-get install -y --no-install-recommends \
        ca-certificates \
        libpq-dev \
        git \
        zip \
        unzip \
    && rm -rf /var/lib/apt/lists/* \
    && openssl req -x509 -nodes -batch -newkey rsa:2048 \
        -keyout /etc/ssl/private/server.key \
        -out /etc/ssl/certs/server.pem \
        -days 365 \
        -subj "/C=US/ST=California/L=Fresno/O=Fresno State/OU=TS/CN=scool.fresnostate.edu" \
    && mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && docker-php-ext-install pgsql \
    && groupmod -g 1000 www-data \
    && usermod -u 1000 www-data

COPY etc/apache2/sites-available/scool.conf /etc/apache2/sites-available/

RUN mkdir /var/www/user_data \
    && a2enmod ssl proxy proxy_http rewrite remoteip \
    && a2dissite 000-default \
    && a2ensite scool \
    && service apache2 restart

WORKDIR /var/www

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY composer.json ./
COPY composer.lock ./
RUN composer install --no-dev

WORKDIR /var/www/html

COPY src/ ./

# see https://httpd.apache.org/docs/2.4/stopping.html#gracefulstop
STOPSIGNAL SIGWINCH

EXPOSE 443
