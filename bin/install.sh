#!/usr/bin/env bash

DB_NAME=wp
DB_USER=root
DB_PASS=root
DB_HOST=$1
WP_VERSION=$2

WP_CORE_DIR=/usr/src/testrunner/wordpress
WP_DEVELOP_DIR=${WP_DEVELOP_DIR-/usr/src/testrunner/wordpress-develop}

set -ex

install_wp() {

	mkdir -p $WP_CORE_DIR

	cd $WP_CORE_DIR

	wp core download --path=$WP_CORE_DIR --version=$WP_VERSION --allow-root
	wp core config --dbname=wp --dbuser=root --dbpass=root --dbhost=$DB_HOST --allow-root
	wp core install --path=$WP_CORE_DIR --url=http://local.dev --title=test --admin_user=admin --admin_password=admin --admin_email=a@aekab.se --skip-email --allow-root

	wget -nv -O $WP_CORE_DIR/wp-content/db.php https://raw.github.com/markoheijnen/wp-mysqli/master/db.php

}

install_test_suite() {

	mkdir -p $WP_DEVELOP_DIR

	# portable in-place argument for both GNU sed and Mac OSX sed
	if [[ $(uname -s) == 'Darwin' ]]; then
		local ioption='-i .bak'
	else
		local ioption='-i'
	fi

	# set up testing suite
	echo "Testsuite, checkout"
	git clone https://github.com/frozzare/wordpress-develop.git $WP_DEVELOP_DIR
	cd $WP_DEVELOP_DIR
	git fetch
	git checkout $WP_BRANCH

	echo "SED wp-config"
	cp $WP_DEVELOP_DIR/wp-tests-config-sample.php $WP_DEVELOP_DIR/wp-tests-config.php
	sed $ioption "s:dirname( __FILE__ ) . '/src/':'$WP_CORE_DIR/':" $WP_DEVELOP_DIR/wp-tests-config.php
	sed $ioption "s/youremptytestdbnamehere/$DB_NAME/" $WP_DEVELOP_DIR/wp-tests-config.php
	sed $ioption "s/yourusernamehere/$DB_USER/" $WP_DEVELOP_DIR/wp-tests-config.php
	sed $ioption "s/yourpasswordhere/$DB_PASS/" $WP_DEVELOP_DIR/wp-tests-config.php
	sed $ioption "s|localhost|${DB_HOST}|" $WP_DEVELOP_DIR/wp-tests-config.php

}

install_wp
install_test_suite

echo "Installation done!"

