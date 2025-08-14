# Étape 1 : image PHP avec Apache
FROM php:8.2-apache

# Autoriser Composer à tourner en root
ENV COMPOSER_ALLOW_SUPERUSER=1

# Installer les dépendances système + extensions PHP
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libicu-dev \
    libzip-dev \
    libpq-dev \
    && docker-php-ext-install intl zip pdo_pgsql pgsql \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Vérifier que les extensions PostgreSQL sont bien installées
RUN php -m | grep -E "(pdo_pgsql|pgsql)" && echo "Extensions PostgreSQL OK" || echo "Extensions PostgreSQL MANQUANTES"

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

# Créer les dossiers et donner les permissions à Apache
RUN mkdir -p var && chown -R www-data:www-data var vendor

# Créer un script de démarrage avec migrations
RUN echo '#!/bin/bash\necho "🚀 Démarrage..."\nphp bin/console doctrine:migrations:migrate --no-interaction || true\necho "🌐 Démarrage Apache..."\nexec apache2-foreground' > /start.sh
RUN chmod +x /start.sh

# Copier la config Apache personnalisée
COPY ./docker/apache/vhost.conf /etc/apache2/sites-available/000-default.conf

# Activer mod_rewrite pour Symfony
RUN a2enmod rewrite

# Exposer le port Apache par défaut
EXPOSE 80

# Utiliser le script de démarrage avec migrations
CMD ["/start.sh"]