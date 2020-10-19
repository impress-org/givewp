#!/usr/bin/env bash

# Add source to make NodeJS downloadable
curl -sL https://deb.nodesource.com/setup_lts.x | sudo -E bash -

# Update package source and install packages
apt-get update
apt-get install php libapache2-mod-php nodejs zip rsync -y

# Install composer
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php -r "if (hash_file('sha384', 'composer-setup.php') === '795f976fe0ebd8b75f26a6dd68f78fd3453ce79f32ecb33e7fd087d39bfeb978342fb73ac986cd4f54edd0dc902601dc') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
php composer-setup.php
php -r "unlink('composer-setup.php');"

# Build & Compile
composer install --no-dev
npm install cross-env
npm install
npm run build

# Remove files that should not be bundled
echo "Removing unwanted files"

rsync -rc --exclude-from="$GITHUB_WORKSPACE/.distignore" "$GITHUB_WORKSPACE/" release/ --delete --delete-excluded

# Zip up the artifact
echo "Zipping Artifact"

cd "${GITHUB_WORKSPACE}/release" || exit
zip -r "${GITHUB_WORKSPACE}/give.zip" .

echo "✅ Artifact Zipped!"

# That's it! Leave the zip for another Action
