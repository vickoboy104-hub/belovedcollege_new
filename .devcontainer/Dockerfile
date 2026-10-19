# Development container for GitHub Codespaces (and any Linux Docker host).
# Everything the app needs is installed here so the image is self-contained and
# reproducible: PHP 8.3 with the extensions the app uses, Composer, Node 22.
FROM mcr.microsoft.com/devcontainers/base:1-ubuntu-24.04

ENV DEBIAN_FRONTEND=noninteractive
ENV COMPOSER_ALLOW_SUPERUSER=1

ARG NODE_VERSION=22.23.2

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        ca-certificates \
        composer \
        curl \
        git \
        gnupg \
        jq \
        libnss-wrapper \
        lsb-release \
        php8.3-bcmath \
        php8.3-cli \
        php8.3-common \
        php8.3-curl \
        php8.3-gd \
        php8.3-intl \
        php8.3-mbstring \
        php8.3-opcache \
        php8.3-readline \
        php8.3-sqlite3 \
        php8.3-xml \
        php8.3-zip \
        sqlite3 \
        unzip \
        wget \
        zip \
    && rm -rf /var/lib/apt/lists/*

# php8.3-cli ships /usr/bin/php8.3; make sure the plain `php` the app scripts
# expect always resolves.
RUN if ! command -v php > /dev/null 2>&1; then \
        ln -s "$(command -v php8.3)" /usr/local/bin/php; \
    fi

RUN printf 'memory_limit = 512M\n\
upload_max_filesize = 64M\n\
post_max_size = 64M\n\
max_execution_time = 120\n' > /etc/php/8.3/cli/conf.d/99-beloved.ini

# Node.js is installed from nodejs.org so the version is pinned and the image
# needs no external devcontainer feature at build time.
RUN curl -fsSL "https://nodejs.org/dist/v${NODE_VERSION}/node-v${NODE_VERSION}-linux-x64.tar.xz" -o /tmp/node.tar.xz \
    && rm -f /usr/local/bin/node /usr/local/bin/npm /usr/local/bin/npx \
    && tar -xJf /tmp/node.tar.xz -C /usr/local --strip-components=1 --no-same-owner \
    && rm -f /tmp/node.tar.xz \
    && node --version \
    && npm --version

WORKDIR /workspaces
