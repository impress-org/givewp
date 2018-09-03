#!/usr/bin/env bash

echo "Updating npm to latest version"
npm i npm@latest -g
echo $?

echo "Make docker-compose.yml executable"
ls -1
chmod +x docker-compose.yml
echo $?

echo "Start docker containers"
sudo docker-compose up -d
echo $?

echo "Set permission to ~/wordpress_data to 777"
sudo chmod 777 -R ~/wordpress_data/
echo $?

# if [ "${TRAVIS_PHP_VERSION:0:3}" != "5.3" ]; then npm i npm@latest -g; fi
# if [ "${TRAVIS_PHP_VERSION:0:3}" != "5.3" ]; then chmod +x docker-compose.yml; fi
# if [ "${TRAVIS_PHP_VERSION:0:3}" != "5.3" ]; then sudo docker-compose up -d; fi
# if [ "${TRAVIS_PHP_VERSION:0:3}" != "5.3" ]; then sudo chmod 777 -R ~/wordpress_data/; fi
