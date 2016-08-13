#!/bin/bash

# Set library info
LIBRARY_NAME="CSVelte"
LIBRARY_VERSION=$TRAVIS_TAG
RELEASE_VERSION="${LIBRARY_NAME}-v${LIBRARY_VERSION}"

if [[ $TRAVIS_PHP_VERSION == "5.6" ]]; then
  vendor/bin/coveralls -v
  wget https://scrutinizer-ci.com/ocular.phar
  php ocular.phar code-coverage:upload --format=php-clover ./build/logs/clover.xml
fi

# Create packages directory
PACKAGES_DIR="${HOME}/packages"
mkdir "$PACKAGES_DIR"

# I don't think the PHP version matters for API docs
wget http://apigen.org/apigen.phar
chmod +x apigen.phar
apigen.phar generate -s src/ -d "${HOME}/apidocs/" --access-levels=public --download --template-theme=bootstrap --title=$LIBRARY_NAME --tree

# Create tarball of API docs
tar -czf "${HOME}/apidocs-${RELEASE_VERSION}.tar.gz" "$HOME/apidocs"

# Set exclude dirs
HIDDEN_FILES='*/\.*'

# Create tarball of library
$PKG="${PACKAGES_DIR}/${RELEASE_VERSION}.tar.gz"
tar -czf . $PKG \
    --exclude=$HIDDEN_FILES \
    --exclude="./docs" \
    --exclude="./tests" \
    --exclude="./travis" \
    --exclude="./phpunit.xml*" \
    --exclude="./vendor"
    --include="./vendor/**/Carbon/*"

# Create zip of library
# zip -r "${PACKAGES_DIR}/${RELEASE_VERSION}.zip" . -x $HIDDEN_FILES \
#     "./docs" \
#     "./tests" \
#     "./travis" \
#     "./phpunit.xml*" \
#     "./vendor"

# Now put tarballs somewhere...

# tips hat to Pádraic Brady, Dave Marshall, Wouter, Graham Campbell for this
