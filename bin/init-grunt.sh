#!/bin/bash

    phpenv config-rm xdebug.ini

    ./"$HOME/.nvm/nvm.sh"
    nvm install v8.9.0
    nvm use v8.9.0

    npm install
    npm install grunt-cli -g

    if [ -f "composer.json" ]; then
        composer selfupdate 1.0.0 --no-interaction
        travis_retry composer install --no-interaction --ignore-platform-reqs
    fi


    mv node_modules/.bin/which node_modules/.bin/which.backup
    rvm install 2.2.0 && rvm use 2.2.0
    mv node_modules/.bin/which.backup node_modules/.bin/which
    gem install sass
    phpenv local --unset


          COMPOSER_LOCATION=$(composer config home --global)
          export PATH="$COMPOSER_LOCATION/vendor/bin:$PATH"


          composer self-update;
              composer global require "squizlabs/php_codesniffer=2.9.*|3.3.*"

              git clone -b master --depth 1 https://github.com/WordPress-Coding-Standards/WordPress-Coding-Standards.git "$HOME/wordpress-coding-standards"
              phpenv rehash
              phpcs --config-set installed_paths "$HOME/wordpress-coding-standards"
              phpenv rehash