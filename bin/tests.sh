#!/usr/bin/env bash

WP_CORE_DIR=/usr/src/wordpress
WP_DEVELOP_DIR=${WP_DEVELOP_DIR-/usr/src/testrunner/wordpress-develop}

set -ex

cd $WP_DEVELOP_DIR
phpunit