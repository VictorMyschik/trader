FROM php:8.4-fpm

RUN pecl install -o -f redis \
&&  rm -rf /tmp/pear \
&&  docker-php-ext-enable redis

RUN pecl install xdebug
RUN docker-php-ext-enable xdebug

RUN apt-get update

RUN apt-get install -y libpq-dev \
    && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
    && docker-php-ext-install pdo pdo_pgsql pgsql \
    && docker-php-ext-install sockets

RUN set -x && \
    apt-get update &&\
    apt-get install --no-install-recommends --no-install-suggests -y \
        openssh-server \
        nodejs \
        default-mysql-client \
        npm \
        bc \
        libzip-dev \
        unzip \
        libbz2-dev \
        libfreetype6-dev \
        libpng-dev \
        libjpeg62-turbo-dev \
        && \
    apt-get install --no-install-recommends --no-install-suggests -y \
        postgresql-client \
        mc \
        && \
    docker-php-ext-configure gd --with-freetype --with-jpeg ; \
    docker-php-ext-install -j$(nproc) \
        bcmath \
        pdo \
        pdo_pgsql \
        bz2 \
        gd \
        exif \
        opcache \
        zip \
        && \
    docker-php-source delete && \
    # composer
    curl -sS https://getcomposer.org/installer | php -- --quiet --install-dir=/usr/local/bin --filename=composer && \
    # clean up
    rm -rf /var/lib/apt/lists/* && \
    rm -rf /usr/share/nginx/html/*

# Cron
RUN apt-get update && apt-get install -y cron
RUN touch /var/log/cron.log && chmod 0644 /var/log/cron.log
COPY ./crontab /etc/cron.d/crontab
RUN chmod 0644 /etc/cron.d/crontab

WORKDIR /var/www/trading

RUN curl -sl https://deb.nodesource.com/setup_22.x | bash -
RUN apt-get install nodejs -y
RUN npm install -g npm@11.1.0

CMD cron && php-fpm
