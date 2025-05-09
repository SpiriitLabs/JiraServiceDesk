FROM composer:lts AS composer-compile

COPY . /go/src/app/dist/app
WORKDIR /go/src/app/dist/app

RUN echo "APP_ENV=prod" > .env.local
RUN composer install --ignore-platform-reqs --optimize-autoloader --no-dev --no-interaction --no-progress --prefer-dist

FROM dunglas/frankenphp:static-builder-musl AS frankenphp-static-builder

COPY --from=composer-compile /go/src/app/dist/app /go/src/app/dist/app

WORKDIR /go/src/app
RUN rm -f dist/cache_key dist/frankenphp-linux-x86_64
RUN EMBED=dist/app \
	NO_COMPRESS=yes \
	./build-static.sh

# COPY files from inside the container to outside
# docker cp $(docker create --name static-app-tmp app-template):/go/src/app/dist/frankenphp-linux-x86_64 my-app ; docker rm static-app-tmp

# Alternative build run this command for auto build and copy the build file to the source directory
# docker run --rm -it -v $PWD:/go/src/app/dist/app -w /go/src/app dunglas/frankenphp:static-builder-musl bash -c 'rm -f dist/cache_key dist/frankenphp-linux-x86_64 && EMBED=dist/app NO_COMPRESS=yes ./build-static.sh && cp -av dist/frankenphp-linux-x86_64 /go/src/app/dist/app/my-app'
