#!/usr/bin/env bash

# Setup PHPUnit in Travis CI environment.

if [ $# -lt 2 ]; then
	echo "Usage: $0 <PHPUnit Version> <PHP Version>"
	exit 1
fi

setup_phpunit() {
  local PHPUNIT_VERSION=$1;
  local PHP_VERSION=$2;

  echo 'PHPUnit Version: '$PHPUNIT_VERSION;
  echo 'PHP Version: '$PHP_VERSION;

  if [[ 'local' = $PHPUNIT_VERSION ]];
  then
    composer install;
  else
    composer config platform.php $PHP_VERSION;
    composer require --dev phpunit/phpunit '^'$PHPUNIT_VERSION;
  fi;
}

setup_phpunit $1 $2;
