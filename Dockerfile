FROM php:7.4-fpm-alpine

RUN apk --no-cache add \
  autoconf \
  bzip2-dev \
  libzip-dev \
  git
RUN set -ex \
  && apk --no-cache add postgresql-dev
RUN docker-php-ext-install pdo_mysql \
    pgsql \
    pdo_pgsql \
    intl 

RUN mv /usr/local/etc/php/php.ini-production /usr/local/etc/php/php.ini

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN chmod 0444 \
  /usr/local/etc/php/php.ini

WORKDIR /var/www/api

EXPOSE 9000/tcp

# copy only specifically what we need
COPY composer.json composer.lock symfony.lock .env* behat.yml.* phpunit.xml.dist ./
COPY bin bin/
COPY config config/
COPY migrations migrations/
COPY public public/
COPY src src/
COPY templates templates/
COPY tests tests/
COPY translations translations/

# Set execution mod on App console binary
RUN chmod +x /var/www/api/bin/console; sync

# Install App dependencies (using composer in PROD env)
RUN set -eux; composer install --prefer-dist --no-progress;

# Clean var cache and log files
RUN set -eux; \
  rm -rf /var/www/api/var/cache /var/www/api/var/log; \
  mkdir -p /var/www/api/var/cache /var/www/api/var/log; \
  chmod 0755 /var/www/api/var/cache /var/www/api/var/log; \
  chown www-data:www-data /var/www/api/var/cache /var/www/api/var/log;

# Define volumes
VOLUME /var/www/api/var/cache
VOLUME /var/www/api/var/log

CMD ["/usr/local/sbin/php-fpm"]
