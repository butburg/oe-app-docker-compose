FROM php:8-fpm-alpine

RUN mkdir -p /var/www/html

WORKDIR /var/www/html

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# Install additional dependencies for GD extension
# oniguruma for mbstring in alpine
# libwebp for webp with gd
RUN apk --no-cache add \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    oniguruma-dev \
    libwebp-dev \
    imagemagick-dev \ 
    $PHPIZE_DEPS

#GD:
#RUN docker-php-ext-configure gd --enable-gd --with-webp=/usr/include/ --with-jpeg=/usr/include/ --with-freetype=/usr/include/
#RUN docker-php-ext-install -j$(nproc) gd

#ImageMagick:
RUN pecl install imagick
RUN docker-php-ext-enable imagick

# Install additional PHP extensions
#RUN docker-php-ext-install -j$(nproc) mbstring exif

RUN sed -i "s/user = www-data/user = root/g" /usr/local/etc/php-fpm.d/www.conf
RUN sed -i "s/group = www-data/group = root/g" /usr/local/etc/php-fpm.d/www.conf
RUN echo "php_admin_flag[log_errors] = on" >> /usr/local/etc/php-fpm.d/www.conf

RUN docker-php-ext-install pdo pdo_mysql

RUN mkdir -p /usr/src/php/ext/redis \
    && curl -L https://github.com/phpredis/phpredis/archive/5.3.4.tar.gz | tar xvz -C /usr/src/php/ext/redis --strip 1 \
    && echo 'redis' >> /usr/src/php-available-exts \
    && docker-php-ext-install redis
    
USER root

CMD ["php-fpm", "-y", "/usr/local/etc/php-fpm.conf", "-R"]
