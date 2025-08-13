# Étape 1 : Utiliser l'image PHP avec Apache
FROM php:8.2-apache

# Installer les dépendances nécessaires
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libicu-dev \
    libzip-dev \
    && docker-php-ext-install intl pdo pdo_mysql zip \
    && docker-php-ext-enable intl pdo_mysql zip

# Installer Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Définir le dossier de travail
WORKDIR /var/www/html

# Copier les fichiers du projet
COPY . .

# Config Git pour éviter les warnings
RUN git config --global --add safe.directory /var/www/html

# Installer les dépendances PHP sans scripts
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Créer le dossier var et donner les permissions
RUN mkdir -p /var/www/html/var && chown -R www-data:www-data /var/www/html

# Copier la configuration Apache personnalisée
COPY ./docker/apache/vhost.conf /etc/apache2/sites-available/000-default.conf

# Activer le module Apache rewrite
RUN a2enmod rewrite

# Exposer le port
EXPOSE 80

# Lancer Apache
CMD ["apache2-foreground"]
