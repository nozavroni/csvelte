#!/bin/bash
# download CSV test files and unzip if they dont already exist
if [ ! -d $SAMPLEDATA_PATH ]; then
    #todo put these files under phpcsv.com/sampledata when you finally get phpcsv.com working...
    wget https://s3-us-west-2.amazonaws.com/csvelte/csvsampledata.tar.gz
    mkdir $SAMPLEDATA_PATH
    tar xvfz csvsampledata.tar.gz -C $SAMPLEDATA_PATH
fi
echo 'date.timezone = "America/Los_Angeles"' >> ~/.phpenv/versions/$(phpenv version-name)/etc/conf.d/travis.ini
