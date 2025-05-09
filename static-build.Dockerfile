# Étape 1 : Utilisation de l'image FrankenPHP pour la construction statique
FROM dunglas/frankenphp:static-builder AS builder

# Étape 2 : Définir le répertoire de travail où l'app sera copiée
WORKDIR /go/src/app

# Étape 3 : Copier tout le code de l'application dans le conteneur
COPY . .

# Étape 4 : Installer les dépendances Composer
RUN composer install --no-dev --optimize-autoloader

# Étape 5 : Construire l'exécutable statique avec FrankenPHP
RUN EMBED=dist/app/ ./build-static.sh

# Étape 6 : Utiliser une image minimale pour la version finale
FROM debian:bullseye-slim

# Étape 7 : Copier le binaire statique depuis l'étape de build
COPY --from=builder /go/src/app/dist/app/my-symfony-app /usr/local/bin/my-symfony-app

# Étape 8 : Exposer le port sur lequel l'application écoute
EXPOSE 8080

# Étape 9 : Exécuter l'application statique
CMD ["my-symfony-app"]
