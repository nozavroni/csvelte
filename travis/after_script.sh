#!/bin/bash
if [[ $TRAVIS_PHP_VERSION == "5.6" ]]; then
  vendor/bin/coveralls -v
  wget https://scrutinizer-ci.com/ocular.phar
  php ocular.phar code-coverage:upload --format=php-clover ./build/logs/clover.xml
fi

# I don't think the PHP version matters for API docs
wget http://apigen.org/apigen.phar
chmod +x apigen.phar
apigen.phar generate -s src/ -d apidocs/ --access-levels=public --download --template-theme=bootstrap --title=CSVelte --tree

# tips hat to Pádraic Brady, Dave Marshall, Wouter, Graham Campbell for this
