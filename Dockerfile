FROM php:7.2-apache

RUN a2dissite 000-default.conf

# Install php modules
RUN apt-get update \
    && apt-get install libyaml-dev -y \
    && pecl install yaml-2.0.2 \
    && docker-php-ext-enable yaml

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN apt-get install -y --no-install-recommends git zip unzip

# Data
VOLUME /data
COPY docker /
COPY . /app/pinedocs
# RUN mv /app/pinedocs/docker /
WORKDIR /app/

# Install Composer dependencies
RUN composer install -d=/app/pinedocs

# Override entrypoint
# To make sure data directory exists and symlinks are set when containers are being run.
RUN ["chmod", "+x", "/usr/bin/entrypoint"]
ENTRYPOINT ["/usr/bin/entrypoint"]
CMD ["apache2-foreground"]
