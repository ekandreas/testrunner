FROM php:5.6-cli

WORKDIR "/tmp"

RUN apt-get update && apt-get install -y \
	mysql-client \
	libmysqlclient-dev \
	git \
	wget \
	zip

RUN curl -sS https://getcomposer.org/installer | php && \
    mv composer.phar /usr/local/bin/composer && \
    chmod +x /usr/local/bin/composer && \
	curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar && \
    chmod +x wp-cli.phar && \
    mv wp-cli.phar /usr/local/bin/wp && \
	curl -O https://phar.phpunit.de/phpunit.phar && \
    chmod +x phpunit.phar && \
    mv phpunit.phar /usr/local/bin/phpunit

RUN docker-php-ext-install mysqli zip

COPY . /usr/src/testrunner

RUN chmod +x /usr/src/testrunner/bin/test.sh 

WORKDIR "/usr/src/testrunner"

ENTRYPOINT ["/usr/src/testrunner/bin/test.sh"]
