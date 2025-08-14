# Étape 1 : image PHP avec Apache
FROM php:8.2-apache

# Autoriser Composer à tourner en root
ENV COMPOSER_ALLOW_SUPERUSER=1

# Installer les dépendances système + extensions PHP pour MySQL
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libicu-dev \
    libzip-dev \
    default-mysql-client \
    && docker-php-ext-install intl zip pdo_mysql mysqli \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Vérifier que les extensions MySQL sont bien installées
RUN php -m | grep -E "(pdo_mysql|mysqli)" && echo "Extensions MySQL OK" || echo "Extensions MySQL MANQUANTES"

# Copier Composer depuis l'image officielle
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Définir le répertoire de travail
WORKDIR /var/www/html

# Copier tout le code
COPY . .

# Configurer Git pour Docker
RUN git config --global --add safe.directory /var/www/html

# Installer les dépendances PHP sans les dev
RUN composer install --no-dev --optimize-autoloader --no-scripts --no-interaction

# Script de démarrage qui fera les migrations
COPY ./docker/start.sh /start.sh
RUN chmod +x /start.sh

# Créer les dossiers et donner les permissions à Apache
RUN mkdir -p var public && chown -R www-data:www-data var vendor public

# Créer les dossiers et donner les permissions à Apache
RUN mkdir -p var public && chown -R www-data:www-data var vendor public

# Vérifier la structure des dossiers
RUN ls -la /var/www/html/

# Copier la config Apache personnalisée
COPY ./docker/apache/vhost.conf /etc/apache2/sites-available/000-default.conf

# Activer mod_rewrite pour Symfony
RUN a2enmod rewrite

# Exposer le port Apache par défaut
EXPOSE 80

# Commande par défaut pour démarrer Apache
CMD ["apache2-foreground"]