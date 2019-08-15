#!/usr/bin/env bash

PHPUNIT_VERSION=$1;
PHP_VERSION=$2;

echo $1;
echo $PHPUNIT_VERSION;

if [[ 'local' = PHPUNIT_VERSION ]];
then
  composer install;
else
  composer config platform.php $PHP_VERSION;
  composer require --dev phpunit/phpunit '^'$PHPUNIT_VERSION;
fi;
