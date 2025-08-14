# Étape 1 : image PHP avec Apache
FROM php:8.2-apache

# Installer les dépendances système
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libicu-dev \
    libzip-dev \
    libpq-dev \
    && docker-php-ext-install intl zip pdo_pgsql pgsql \
    && docker-php-ext-enable intl zip pdo_pgsql pgsql \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Copier Composer depuis l'image officielle
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Définir le répertoire de travail
WORKDIR /var/www/html

# Copier tout le code
COPY . .

# Configurer Git pour Docker
RUN git config --global --add safe.directory /var/www/html

# Installer les dépendances PHP sans les dev
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Créer le dossier var et donner les permissions à Apache
RUN mkdir -p var && chown -R www-data:www-data /var/www/html

# Copier la config Apache si tu en as une personnalisée
COPY ./docker/apache/vhost.conf /etc/apache2/sites-available/000-default.conf

# Activer mod_rewrite pour Symfony
RUN a2enmod rewrite

# Exposer le port Apache par défaut
EXPOSE 80

# Commande par défaut pour démarrer Apache
CMD ["apache2-foreground"]
