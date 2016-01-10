#!/usr/bin/env bash

WP_CORE_DIR=/tmp/wordpress
WP_DEVELOP_DIR=${WP_DEVELOP_DIR-/tmp/wordpress-develop}

set -ex

cd $WP_DEVELOP_DIR
phpunit