#!/usr/bin/env bash

DB_HOST=$1
WP_VERSION=$2

WP_DEVELOP_DIR=${WP_DEVELOP_DIR-/usr/src/wordpress-develop}

set -ex

install_test_suite() {

	rm -Rf $WP_DEVELOP_DIR
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
	sed $ioption "s:dirname( __FILE__ ) . '/src/':'$WP_DEVELOP_DIR/src/':" $WP_DEVELOP_DIR/wp-tests-config.php
	sed $ioption "s/youremptytestdbnamehere/wp/" $WP_DEVELOP_DIR/wp-tests-config.php
	sed $ioption "s/yourusernamehere/root/" $WP_DEVELOP_DIR/wp-tests-config.php
	sed $ioption "s/yourpasswordhere/root/" $WP_DEVELOP_DIR/wp-tests-config.php
	sed $ioption "s|localhost|${DB_HOST}|" $WP_DEVELOP_DIR/wp-tests-config.php

}

install_test_suite

echo "Installation done!"

