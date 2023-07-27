FROM mcr.microsoft.com/devcontainers/php:8.2
LABEL stage=builder

COPY src/000-default.conf /etc/apache2/sites-available/000-default.conf
COPY src/dokuwiki.conf /etc/apache2/conf-enabled/dokuwiki.conf

ADD https://ch.tudelft.nl/certs/wisvch.crt /usr/local/share/ca-certificates/wisvch.crt
RUN chmod 0644 /usr/local/share/ca-certificates/wisvch.crt && \
    update-ca-certificates

RUN apt-get update && \
    apt-get install -y --no-install-recommends wget libxml2-dev unzip && \
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

RUN wget https://download.dokuwiki.org/src/dokuwiki/dokuwiki-stable.tgz && \
    tar xvf dokuwiki-stable.tgz && \
    mv dokuwiki-*/ dokuwiki && \
    chown -R www-data:www-data /var/www/dokuwiki

# run apache2 as non-root
#RUN useradd --no-log-init -r -g www-data --uid=4001 dokuwiki
#ENV APACHE_RUN_USER=dokuwiki
#ENV APACHE_RUN_GROUP=www-data
#USER 4001:33

# Install dependencies
WORKDIR /var/www/dokuwiki
ADD src/composer.json /var/www/dokuwiki/composer.json
RUN composer install

# Add DokuWiki plugins
WORKDIR /var/www

# Make plugins directory
# ADD src/plugins /var/www/dokuwiki/lib/plugins
# PLUGINS ARE MOUNTED

# Load the configurations
ADD src/conf /var/www/dokuwiki/conf

# Set permissions
RUN chown -R www-data:www-data /var/www/dokuwiki/lib/plugins
RUN chown -R www-data:www-data /var/www/dokuwiki/conf

# !!! Fuck it its a devcontainer. I cannot get apache to run as www-data.
RUN chmod -R 777 /var/www/dokuwiki/data/


# Run apache
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf
# EXPOSE 8080

USER www-data
# Apache run group
ENV APACHE_RUN_USER www-data
ENV APACHE_RUN_GROUP www-data
