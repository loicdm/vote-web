FROM php:8.2-apache

# Installer les extensions nécessaires
RUN apt-get update && apt-get install -y libpng-dev libonig-dev libxml2-dev libzip-dev \
  && docker-php-ext-install pdo pdo_mysql mysqli && apt-get install -y git make gcc

RUN git clone https://github.com/loicdm/c-votes/ /c-votes
WORKDIR /c-votes
RUN git pull
RUN make -B && cp target/main /usr/bin/depouilleur

WORKDIR /var/www/html

# Copier les fichiers de l'application
COPY . /var/www/html/

# Activer Apache rewrite si nécessaire
RUN a2enmod rewrite

