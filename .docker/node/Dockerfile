FROM node:20-alpine

RUN apk add pkgconfig autoconf automake libtool nasm build-base zlib-dev curl gcompat
RUN corepack enable
RUN corepack prepare pnpm@latest-9 --activate

COPY .npmrc /home/node/./
