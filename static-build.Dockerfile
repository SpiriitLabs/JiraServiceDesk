# Étape 1 : Utiliser l'image FrankenPHP pour la compilation de l'exécutable statique
FROM dunglas/frankenphp:static-builder AS builder

# Étape 2 : Définir le répertoire de travail
WORKDIR /go/src/app

# Étape 3 : Copier tout le code de ton projet Symfony dans le conteneur
COPY . .

# Étape 4 : Installer les dépendances PHP avec Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer install --no-dev --optimize-autoloader

# Étape 5 : Compiler l'application Symfony en un exécutable statique
RUN EMBED=dist/app/ ./build-static.sh

# Étape 6 : Utiliser une image minimaliste pour le runtime
FROM debian:bullseye-slim

# Étape 7 : Copier le binaire statique de l'application Symfony
COPY --from=builder /go/src/app/dist/app/my-symfony-app /usr/local/bin/my-symfony-app

# Étape 8 : Exposer le port 8080 (si ton application écoute sur ce port)
EXPOSE 8080

# Étape 9 : Démarrer l'application Symfony via l'exécutable statique
CMD ["my-symfony-app"]
