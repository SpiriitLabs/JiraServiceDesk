FROM node:20-alpine

ENV PNPM_HOME="/pnpm"
ENV PATH="$PNPM_HOME:$PATH"

RUN apk add pkgconfig autoconf automake libtool nasm build-base zlib-dev curl gcompat
RUN corepack enable
RUN corepack prepare pnpm@latest-10 --activate

COPY .npmrc /home/node/./
