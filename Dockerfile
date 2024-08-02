FROM php:8.3-apache-bookworm

ENV OHRM_VERSION 5.7
ENV OHRM_MD5 5bd924a546e29e06c34eec73b014d139

# Mise à jour du système et installation des paquets nécessaires
RUN apt-get update && apt-get install -y \
    libfreetype6-dev \
    libjpeg-dev \
    libpng-dev \
    libzip-dev \
    libldap2-dev \
    libicu-dev \
    unzip

# Configuration PHP et Apache
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Installation des extensions PHP requises
RUN set -ex; \
    docker-php-ext-configure gd --with-freetype --with-jpeg; \
    docker-php-ext-configure ldap --with-libdir=lib/$(uname -m)-linux-gnu/; \
    docker-php-ext-install -j "$(nproc)" gd opcache intl pdo_mysql zip ldap

# Nettoyage
RUN apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Copie des fichiers de l'application
COPY ./cvtheque /var/www/html

# Copie du script de démarrage
COPY start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

# Configuration des permissions
RUN set -ex; \
    mkdir -p /var/www/html/src/cache /var/www/html/src/log /var/www/html/src/config; \
    chown www-data:www-data /var/www/html; \
    chown -R www-data:www-data /var/www/html/src/cache /var/www/html/src/log /var/www/html/src/config; \
    chmod -R 775 /var/www/html/src/cache /var/www/html/src/log /var/www/html/src/config

# Ajout de la directive ServerName pour Apache
RUN echo "ServerName demo.cvtheque" >> /etc/apache2/apache2.conf

# Copie de la configuration de l'hôte virtuel
COPY cvtheque.conf /etc/apache2/sites-available/

# Activation de l'hôte virtuel et des modules Apache nécessaires
RUN a2ensite cvtheque.conf && a2enmod rewrite

# Configuration de PHP
RUN { \
        echo 'opcache.memory_consumption=128'; \
        echo 'opcache.interned_strings_buffer=8'; \
        echo 'opcache.max_accelerated_files=4000'; \
        echo 'opcache.revalidate_freq=60'; \
        echo 'opcache.fast_shutdown=1'; \
        echo 'opcache.enable_cli=1'; \
    } > /usr/local/etc/php/conf.d/opcache-recommended.ini

# Définir les variables d'environnement Apache
ENV APACHE_RUN_USER www-data
ENV APACHE_RUN_GROUP www-data
ENV APACHE_LOG_DIR /var/log/apache2
ENV APACHE_PID_FILE /var/run/apache2/apache2.pid
ENV APACHE_RUN_DIR /var/run/apache2
ENV APACHE_LOCK_DIR /var/lock/apache2
ENV APACHE_SERVERADMIN ${SERVER_ADMIN}

# Créer les répertoires Apache nécessaires
RUN mkdir -p /var/run/apache2 /var/lock/apache2 /var/log/apache2

# Modifier la configuration Apache pour écouter sur le port 8080
RUN sed -i 's/Listen 80/Listen 8080/' /etc/apache2/ports.conf

# Exposer le port 8080
EXPOSE 8080

# Définir les volumes pour les répertoires à préserver
VOLUME ["/var/www/html/lib", "/var/www/html/src/cache"]

# Utiliser le script de démarrage comme point d'entrée
CMD ["/usr/local/bin/start.sh"]