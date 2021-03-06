FROM php:5.6-cli

COPY . /usr/src/testrunner

WORKDIR "/tmp"

RUN apt-get update && apt-get install -y \
    mysql-client \
    libmysqlclient-dev \
    git \
    zip

RUN curl -sS https://getcomposer.org/installer | php && \
    mv composer.phar /usr/local/bin/composer && \
    chmod +x /usr/local/bin/composer

RUN curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar && \
    chmod +x wp-cli.phar && \
    mv wp-cli.phar /usr/local/bin/wp

#RUN    curl -O https://phar.phpunit.de/phpunit.phar && \
#    chmod +x phpunit.phar && \
#    mv phpunit.phar /usr/local/bin/phpunit

RUN docker-php-ext-install mysqli zip mbstring

RUN pecl install xdebug-beta

RUN docker-php-ext-enable xdebug

RUN mkdir /usr/src/plugin

WORKDIR "/usr/src/testrunner"

RUN chmod +x /usr/src/testrunner/bin/install.sh && \
    chmod +x /usr/src/testrunner/bin/tests.sh
