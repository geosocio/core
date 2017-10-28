# Builder
FROM composer as builder
ENV APP_ENV prod
COPY ./ /app
RUN composer --no-dev install

# Service
FROM php:7.1-apache

RUN a2enmod rewrite env

# System Dependencies.
RUN apt-get update && apt-get install -y \
        libicu-dev \
	--no-install-recommends && rm -r /var/lib/apt/lists/*

# install the PHP extensions we need
RUN set -ex \
	&& buildDeps=' \
		uuid-dev \
	' \
	&& apt-get update && apt-get install -y --no-install-recommends $buildDeps && rm -rf /var/lib/apt/lists/* \
    && pecl channel-update pecl.php.net \
    && pecl install uuid \
    && docker-php-ext-enable uuid \
	&& apt-get purge -y --auto-remove $buildDeps

RUN set -ex \
	&& buildDeps=' \
		libsqlite3-dev \
	' \
	&& apt-get update && apt-get install -y --no-install-recommends $buildDeps && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-install intl opcache pdo_mysql pdo_sqlite \
	&& apt-get purge -y --auto-remove $buildDeps

# set recommended PHP.ini settings
# see https://secure.php.net/manual/en/opcache.installation.php
RUN { \
		echo 'opcache.memory_consumption=128'; \
		echo 'opcache.interned_strings_buffer=8'; \
		echo 'opcache.max_accelerated_files=4000'; \
		echo 'opcache.revalidate_freq=60'; \
		echo 'opcache.fast_shutdown=1'; \
		echo 'opcache.enable_cli=1'; \
	} > /usr/local/etc/php/conf.d/opcache-recommended.ini

# Environment
ENV APP_ENV prod
ENV JWT_PASSPHRASE 87155686a4a5dd1cfd04daf3ba1f5af8

# Copy the app and all the dependencies
COPY --from=builder /app /var/www

# Generate a Key for JWT
RUN mkdir -p ../config/jwt \
    && openssl genrsa -out ../config/jwt/private.pem -aes256 -passout "pass:${JWT_PASSPHRASE}" 4096 \
    && openssl rsa -pubout -passin "pass:${JWT_PASSPHRASE}" -in ../config/jwt/private.pem -out ../config/jwt/public.pem

# Touch the SQLite Database and set the permissions
RUN mkdir -p ../var \
    && touch ../var/data.db \
    && chown www-data:www-data ../var/data.db

# Create the database schema and load the fixtures
RUN ../bin/console doctrine:schema:create \
    && ../bin/console doctrine:fixtures:load --fixtures=../src/DataFixtures/ORM\
