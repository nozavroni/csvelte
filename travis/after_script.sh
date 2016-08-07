#!/bin/bash
if [[ $TRAVIS_PHP_VERSION == "5.6" ]]; then
  vendor/bin/coveralls -v
  wget https://scrutinizer-ci.com/ocular.phar
  php ocular.phar code-coverage:upload --format=php-clover ./build/logs/clover.xml
fi

# tips hat to Pádraic Brady, Dave Marshall, Wouter, Graham Campbell for this
