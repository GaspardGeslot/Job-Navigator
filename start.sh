#!/bin/bash
set -e

# Créer les volumes nommés s'ils n'existent pas
docker volume create --name lib_data || true
docker volume create --name cache_data || true

# Monter les volumes
mountpoint -q /var/www/html/lib || mount --bind $(docker volume inspect --format '{{ .Mountpoint }}' lib_data) /var/www/html/lib
mountpoint -q /var/www/html/src/cache || mount --bind $(docker volume inspect --format '{{ .Mountpoint }}' cache_data) /var/www/html/src/cache

# Démarrer Apache
apache2ctl -D FOREGROUND