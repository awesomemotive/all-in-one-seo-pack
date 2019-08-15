#!/usr/bin/env bash

PHPUNIT_VERSION = $1;
PHP_VERSION     = $2;

if [[ 'local' = PHPUNIT_VERSION ]];
then
  composer install;
else
  composer config platform.php PHP_VERSION;
  composer config require-dev.phpunit/phpunit PHPUNIT_VERSION;
fi;
