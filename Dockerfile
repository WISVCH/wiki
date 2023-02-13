FROM php:8.2-apache
LABEL stage=builder

COPY 000-default.conf /etc/apache2/sites-available/000-default.conf
COPY dokuwiki.conf /etc/apache2/conf-enabled/dokuwiki.conf

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
    chown -R www-data:www-data /var/www/dokuwiki && \
    sed -i 's/Listen 80/Listen 8080/g' /etc/apache2/ports.conf

# run apache2 as non-root
#RUN useradd --no-log-init -r -g www-data --uid=4001 dokuwiki
#ENV APACHE_RUN_USER=dokuwiki
#ENV APACHE_RUN_GROUP=www-data
#USER 4001:33


# Add DokuWiki plugins

# Make plugins directory
RUN mkdir -p /var/www/dokuwiki/lib/plugins

RUN wget https://github.com/cosmocode/dokuwiki-plugin-oauth/zipball/master -O oauth.zip && \
    unzip oauth.zip -d /var/www/dokuwiki/lib/plugins/ && \
    mv /var/www/dokuwiki/lib/plugins/cosmocode-dokuwiki-plugin-oauth-* /var/www/dokuwiki/lib/plugins/oauth && \
    rm oauth.zip && \
    chown -R www-data:www-data /var/www/dokuwiki/lib/plugins/oauth

RUN wget https://github.com/cosmocode/dokuwiki-plugin-oauthgeneric/zipball/master -O oauthgeneric.zip && \
    unzip oauthgeneric.zip -d /var/www/dokuwiki/lib/plugins/ && \
    mv /var/www/dokuwiki/lib/plugins/cosmocode-dokuwiki-plugin-oauthgeneric-* /var/www/dokuwiki/lib/plugins/oauthgeneric && \
    rm oauthgeneric.zip && \
    chown -R www-data:www-data /var/www/dokuwiki/lib/plugins/oauthgeneric

# Load the configurations
ADD ./conf /var/www/dokuwiki/conf
# Set permissions
RUN chown -R www-data:www-data /var/www/dokuwiki/conf
