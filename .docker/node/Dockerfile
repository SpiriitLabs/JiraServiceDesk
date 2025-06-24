FROM node:20-alpine
ARG APP_USER_ID
ARG APP_GROUP_ID

RUN apk add pkgconfig autoconf automake libtool nasm build-base zlib-dev curl gcompat
RUN corepack enable
RUN corepack prepare pnpm@latest-9 --activate

RUN if getent group ${APP_GROUP_ID}; then \
        echo "GID ${APP_GROUP_ID} already exists, choosing another GID"; \
        APP_GROUP_ID=$(($APP_GROUP_ID + 1)); \
        APP_USER_ID=$((APP_USER_ID + 1)); \
    fi \
    && addgroup -g ${APP_GROUP_ID} project \
    && adduser -u ${APP_USER_ID} -G project -D project

#RUN addgroup -g ${APP_GROUP_ID} project && adduser -u ${APP_USER_ID} -G project -D project

COPY .npmrc /home/node/./

USER project
