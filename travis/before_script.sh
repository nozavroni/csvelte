#!/bin/bash

# @todo Delete these lines entirely once you're sure they are no longer necessary...
# wget https://s3-us-west-2.amazonaws.com/csvelte/csvsampledata.tar.gz
# tar xvfz csvsampledata.tar.gz -C tests/

echo 'date.timezone = "America/Los_Angeles"' >> ~/.phpenv/versions/$(phpenv version-name)/etc/conf.d/travis.ini
