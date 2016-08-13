#!/bin/bash
# download CSV test files and unzip if they dont already exist
if [ ! -d "$HOME/sampledata" ]; then
    #todo put these files under phpcsv.com/sampledata when you finally get phpcsv.com working...
    wget https://s3-us-west-2.amazonaws.com/csvelte/csvsampledata.tar.gz
    mkdir $HOME/sampledata
    tar xvfz csvsampledata.tar.gz -C $HOME/sampledata
fi
echo 'date.timezone = "America/Los_Angeles"' >> ~/.phpenv/versions/$(phpenv version-name)/etc/conf.d/travis.ini

cp -R $HOME/sampledata/files tests/files
