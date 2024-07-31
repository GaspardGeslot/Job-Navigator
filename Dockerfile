FROM php:8.3-apache-bookworm

ENV OHRM_VERSION 5.7
ENV OHRM_MD5 5bd924a546e29e06c34eec73b014d139

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

RUN set -ex; \
    savedAptMark="$(apt-mark showmanual)"; \
    apt-get update; \
    apt-get install -y --no-install-recommends \
        libfreetype6-dev \
        libjpeg-dev \
        libpng-dev \
        libzip-dev \
        libldap2-dev \
        libicu-dev \
        unzip \
    ; \
    docker-php-ext-configure gd --with-freetype --with-jpeg; \
    docker-php-ext-configure ldap \
        --with-libdir=lib/$(uname -m)-linux-gnu/ \
    ; \
    docker-php-ext-install -j "$(nproc)" \
        gd \
        opcache \
        intl \
        pdo_mysql \
        zip \
        ldap \
    ; \
    apt-mark auto '.*' > /dev/null; \
    apt-mark manual $savedAptMark; \
    ldd "$(php -r 'echo ini_get("extension_dir");')"/*.so \
        | awk '/=>/ { so = $(NF-1); if (index(so, "/usr/local/") == 1) { next }; gsub("^/(usr/)?", "", so); print so }' \
        | sort -u \
        | xargs -r dpkg-query -S \
        | cut -d: -f1 \
        | sort -u \
        | xargs -rt apt-mark manual; \
    apt-get purge -y --auto-remove -o APT::AutoRemove::RecommendsImportant=false; \
    rm -rf /var/cache/apt/archives; \
    rm -rf /var/lib/apt/lists/*

COPY ./cvtheque /var/www/html

RUN set -ex; \
    mkdir -p /var/www/html/src/cache /var/www/html/src/log /var/www/html/src/config; \
    chown www-data:www-data /var/www/html; \
    chown -R www-data:www-data /var/www/html/src/cache /var/www/html/src/log /var/www/html/src/config; \
    chmod -R 775 /var/www/html/src/cache /var/www/html/src/log /var/www/html/src/config;

RUN { \
        echo 'opcache.memory_consumption=128'; \
        echo 'opcache.interned_strings_buffer=8'; \
        echo 'opcache.max_accelerated_files=4000'; \
        echo 'opcache.revalidate_freq=60'; \
        echo 'opcache.fast_shutdown=1'; \
        echo 'opcache.enable_cli=1'; \
    } > /usr/local/etc/php/conf.d/opcache-recommended.ini; \
    if command -v a2enmod; then \
        a2enmod rewrite; \
    fi;

VOLUME ["/var/www/html"]