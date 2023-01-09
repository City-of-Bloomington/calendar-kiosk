FROM ubuntu:latest
ENV DEBIAN_FRONTEND=noninteractive
ENV TZ=America/Indiana/Indianapolis
RUN ln -snf /usr/share/zoneinfo/America/$TZ /etc/localtime \
    && echo $TZ > /etc/timezone

RUN apt-get update && apt-get install -y apache2 locales \
    && locale-gen en_US.UTF-8
RUN a2enmod alias && \
    a2enmod headers && \
    a2enmod remoteip && \
    a2enmod rewrite

RUN apt-get install -y \
    php-common \
    composer \
    php-cli \
    php-dom \
    php-json \
    php-readline \
    php-mbstring \
    php-pgsql \
    php-intl \
    php-zip \
    unzip \
    php-curl \
    php-ldap \
    php-xsl \
    libapache2-mod-php

# we could copy this directly using the COPY directive, but I want it to be as close to
#  the ansible deployment as possible in how it deploys
COPY build/calendar-kiosk /usr/local/src/calendar-kiosk
RUN mkdir -p /srv/data/calendar-kiosk && cp -r /usr/local/src/calendar-kiosk/data/* /srv/data/calendar-kiosk/
RUN mkdir -p /srv/sites/calendar-kiosk && cp -r /usr/local/src/calendar-kiosk/* /srv/sites/calendar-kiosk/
RUN chown --recursive www-data:www-data /srv/sites/calendar-kiosk
RUN chown --recursive www-data:www-data /srv/data/calendar-kiosk
RUN rm -rf /usr/src/calendar-kiosk

WORKDIR /srv/sites/calendar-kiosk


COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf
COPY docker/log-to-stdout.conf /etc/apache2/conf-available
COPY docker/site_config.php /srv/data/calendar-kiosk/
COPY docker/credentials.json /srv/data/calendar-kiosk/
RUN ln -s /srv/sites/calendar-kiosk/data/Themes/COB/public /srv/sites/calendar-kiosk/public/COB
RUN a2enconf log-to-stdout
#RUN ln -sfT /dev/stdout "/var/log/apache2/other_vhosts_access.log"
#RUN chown -R www-data:www-data /var/log/apache2

# it's actually faster to put the vendor folder into the .dockerignore
#  and then just include a composer run as part of the build
USER www-data
RUN composer install

USER root

EXPOSE 80
ENTRYPOINT ["apachectl", "-D", "FOREGROUND"]
