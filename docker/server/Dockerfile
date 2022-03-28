#FROM oberd/php-8.0-apache
FROM oberd/php-7.4-apache

RUN a2enmod rewrite
RUN apt-get update && apt-get install -y git unzip zip
WORKDIR /var/www/html
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions gd pdo_mysql bcmath zip intl opcache
COPY --from=composer:2.0 /usr/bin/composer /usr/local/bin/composer
RUN apt update 
RUN apt install nano

# install the symfony cli
RUN curl -sS https://get.symfony.com/cli/installer | bash
RUN mv /root/.symfony/bin/symfony /usr/local/bin/symfony

# install the PHP APCu extension
RUN mkdir -p /usr/src/php/ext/apcu && curl -fsSL https://pecl.php.net/get/apcu | tar xvz -C "/usr/src/php/ext/apcu" --strip 1 && docker-php-ext-install apcu

# Install Xdebug
RUN pecl install xdebug-3.0.1 && docker-php-ext-enable xdebug

# Install cron
RUN apt-get update
RUN apt-get -y install cron
# /etc/init.d/cron start