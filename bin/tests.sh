#!/usr/bin/env bash
set -ex

export WP_DEVELOP_DIR="/usr/src/testrunner/wordpress-develop"
cd /usr/src/testrunner/wordpress-develop/src/wp-content/plugins
mkdir -p theplugin
cd theplugin
rsync -rv --exclude={vendor,.git} /usr/src/plugin/. .
composer update --no-dev --prefer-dist
remove phpunit/phpunit
phpunit > testresult.txt