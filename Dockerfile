FROM php:8.5.9-apache
LABEL stage=builder

COPY src/000-default.conf /etc/apache2/sites-available/000-default.conf
COPY src/dokuwiki.conf /etc/apache2/conf-enabled/dokuwiki.conf

RUN apt-get update && \
    apt-get install -y --no-install-recommends wget libgmp-dev libxml2-dev unzip && \
    docker-php-ext-install gmp && \
    apt-get purge -y --auto-remove -o APT::AutoRemove::RecommendsImportant=false && \
    rm -rf /var/lib/apt/lists/*

RUN a2enmod rewrite

# set recommended PHP.ini settings
# see https://secure.php.net/manual/en/opcache.installation.php
RUN { \
    echo 'opcache.memory_consumption=128'; \
    echo 'opcache.interned_strings_buffer=8'; \
    echo 'opcache.max_accelerated_files=4000'; \
    echo 'opcache.revalidate_freq=2'; \
    echo 'opcache.fast_shutdown=1'; \
    echo 'opcache.enable_cli=1'; \
    # Disable PHP errors
    echo 'display_errors=Off'; \
    } > /usr/local/etc/php/conf.d/opcache-recommended.ini

WORKDIR /var/www

ARG DOKUWIKI_RELEASE=2026-07-14a
RUN wget "https://github.com/dokuwiki/dokuwiki/releases/download/release-${DOKUWIKI_RELEASE}/dokuwiki-${DOKUWIKI_RELEASE}.tgz" && \
    tar xvf "dokuwiki-${DOKUWIKI_RELEASE}.tgz" && \
    mv dokuwiki-*/ dokuwiki && \
    chown -R www-data:www-data /var/www/dokuwiki && \
    sed -i 's/Listen 80/Listen 8080/g' /etc/apache2/ports.conf

# run apache2 as non-root
#RUN useradd --no-log-init -r -g www-data --uid=4001 dokuwiki
#ENV APACHE_RUN_USER=dokuwiki
#ENV APACHE_RUN_GROUP=www-data
#USER 4001:33

# Install dependencies
WORKDIR /var/www/dokuwiki
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
COPY src/composer.json src/composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --no-progress

# Add DokuWiki plugins
WORKDIR /var/www
# Make plugins directory
ADD src/plugins /var/www/dokuwiki/lib/plugins

# Load the configurations
ADD src/conf /var/www/dokuwiki/conf

# Set permissions
RUN chown -R www-data:www-data /var/www/dokuwiki/lib/plugins
RUN chown -R www-data:www-data /var/www/dokuwiki/conf
