#!/bin/bash

# Script de démarrage : docker/start.sh

echo "🚀 Démarrage de l'application..."

# Faire les migrations de base de données
echo "📦 Exécution des migrations..."
php bin/console doctrine:migrations:migrate --no-interaction

# Démarrer Apache
echo "🌐 Démarrage d'Apache..."
apache2-foreground