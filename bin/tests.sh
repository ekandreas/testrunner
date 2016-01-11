#!/usr/bin/env bash
set -ex

export WP_DEVELOP_DIR="/usr/src/testrunner/wordpress-develop"
cd /usr/src/testrunner/wordpress-develop/src/wp-content/plugins
mkdir -p theplugin
cd theplugin
rsync -r --exclude={vendor,.git} /usr/src/plugin/. .
rm -Rf vendor
composer update
vendor/bin/phpunit > testresult.txt