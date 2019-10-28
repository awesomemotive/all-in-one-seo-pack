#!/usr/bin/env bash

npm install
npm install grunt -g
npm install grunt-cli -g
npm install grunt-mkdir grunt-phpcs grunt-phpcbf grunt-phplint grunt-contrib-jshint grunt-contrib-uglify grunt-eslint

composer install --no-interaction --ignore-platform-reqs

COMPOSER_LOCATION=$(composer config home --global)
export PATH="$COMPOSER_LOCATION/vendor/bin:$PATH"
composer self-update
