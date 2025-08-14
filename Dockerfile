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

# Créer les dossiers avec les bonnes permissions
RUN mkdir -p var/cache var/log public && \
    chown -R www-data:www-data var vendor public && \
    chmod -R 777 var

# Configuration PHP pour éviter les problèmes de permissions
RUN echo 'memory_limit = 256M' > /usr/local/etc/php/conf.d/memory.ini
RUN echo 'opcache.enable_cli=1' > /usr/local/etc/php/conf.d/opcache.ini

# Créer un script de démarrage avec permissions maximales
RUN echo '#!/bin/bash\necho "🚀 Démarrage..."\necho "🔧 Fix permissions agressif..."\nchmod -R 777 /var/www/html/var/ || true\nchmod -R 777 /tmp/ || true\nmkdir -p /var/www/html/var/cache/prod || true\nchmod -R 777 /var/www/html/var/cache/ || true\necho "📦 Migrations..."\nphp bin/console doctrine:migrations:migrate --no-interaction || true\necho "🗑️ Clear cache..."\nrm -rf /var/www/html/var/cache/* || true\necho "🌐 Démarrage Apache..."\nexec apache2-foreground' > /start.sh
RUN chmod +x /start.sh

# Copier la config Apache personnalisée
COPY ./docker/apache/vhost.conf /etc/apache2/sites-available/000-default.conf

# Activer mod_rewrite pour Symfony
RUN a2enmod rewrite

# Exposer le port Apache par défaut
EXPOSE 80

# Utiliser le script de démarrage avec migrations
CMD ["/start.sh"]