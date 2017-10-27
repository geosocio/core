FROM php:7.1-apache

RUN a2enmod rewrite

# System Dependencies.
RUN apt-get update && apt-get install -y \
        git \
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
    && docker-php-ext-install intl opcache pdo_mysql pdo_sqlite zip \
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

# Install Composer.
RUN curl -o /tmp/composer-setup.php https://getcomposer.org/installer \
    && curl -o /tmp/composer-setup.sig https://composer.github.io/installer.sig \
    && php -r "if (hash('SHA384', file_get_contents('/tmp/composer-setup.php')) !== trim(file_get_contents('/tmp/composer-setup.sig'))) { unlink('/tmp/composer-setup.php'); echo 'Invalid installer' . PHP_EOL; exit(1); }" \
    && php /tmp/composer-setup.php --no-ansi --install-dir=/usr/local/bin --filename=composer \
    && rm -rf /tmp/composer-setup.php

COPY ./ /var/www

RUN composer --no-dev --working-dir=../ install
