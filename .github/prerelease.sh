#!/usr/bin/env bash

# Add source to make NodeJS downloadable
curl -sL https://deb.nodesource.com/setup_lts.x | sudo -E bash -

# Update package source and install packages
apt-get update
apt-get install php libapache2-mod-php nodejs zip -y

# Install composer
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php -r "if (hash_file('sha384', 'composer-setup.php') === '795f976fe0ebd8b75f26a6dd68f78fd3453ce79f32ecb33e7fd087d39bfeb978342fb73ac986cd4f54edd0dc902601dc') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
php composer-setup.php
php -r "unlink('composer-setup.php');"

# Build & Compile
composer install
npm install cross-env
npm install
npm run build

# Remove files that should not be bundled
echo "Removing unwanted files"

while IFS= read -r file ; do rm -- "$file" ; done < .releaseignore

# Zip up the artifact
echo "Zipping Artifact"

zip -r give.zip ./*

# That's it! Leave the zip for another Action
