#!/usr/bin/env bash
set -ex

export WP_DEVELOP_DIR="/usr/src/wordpress-develop"
cd /usr/src/wordpress-develop/src/wp-content/plugins
mkdir -p theplugin
cd theplugin
rsync -rv --exclude={vendor,.git} /usr/src/plugin/. .
composer update --no-dev --prefer-dist
phpunit > testresult.txt