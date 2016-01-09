FROM php:5.5-apache

MAINTAINER Andreas Ek <andreas@aekab.se>

RUN a2enmod rewrite

RUN apt-get update && apt-get install -y mysql-client libmysqlclient-dev git subversion

RUN curl -sS https://getcomposer.org/installer | php && \
    mv composer.phar /usr/local/bin/composer

RUN curl -L https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar > /usr/local/bin/wp

RUN docker-php-ext-install mysqli

ADD docker.conf /etc/apache2/sites-enabled/

EXPOSE 80