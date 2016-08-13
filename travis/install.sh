#!/bin/bash
if [[ $TRAVIS_PHP_VERSION == "5.6" ]]; then
  composer require --dev satooshi/php-coveralls:~0.7@dev
fi

# Install dependencies 
composer install -n

# tips hat to Pádraic Brady, Dave Marshall, Wouter, Graham Campbell for this
