#!/usr/bin/env bash

# Gets the PHPUnit version compatable with PHP.

if [ $# -lt 1 ]; then
	echo "Usage: $0 <PHP Version>"
	exit 1
fi

get_phpunit_compatable_version() {
  local PHP_VERSION=$1;

  # Set PHPUnit version based on which PHP version is in use.
  if [[ "7.3" = "$PHP_VERSION" || "7.2" = "$PHP_VERSION" || "7.1" = "$PHP_VERSION" ]]; then
    local PHPUNIT_VERSION=7;
  elif [[ "7.0" = "$PHP_VERSION" ]]; then
    local PHPUNIT_VERSION=6;
  elif [[ "5.6" = "$PHP_VERSION" ]]; then
    local PHPUNIT_VERSION=5;
  elif [[ "5.5" = "$PHP_VERSION" || "5.4" = "$PHP_VERSION" ]]; then
    local PHPUNIT_VERSION=4;
  else
    local PHPUNIT_VERSION=0;
  fi

  echo "$PHPUNIT_VERSION";
}

echo $(get_phpunit_compatable_version $1);
