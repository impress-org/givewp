#!/usr/bin/env bash

echo "Give Log: current branch is ${TRAVIS_BRANCH}";
echo "${TRAVIS_PHP_VERSION:0:3}";
echo "${TRAVIS_EVENT_TYPE}"

echo 'Give Log: setup and run frontend tests';

until $(curl --output /dev/null --silent --head --fail http://localhost:8004); do printf '.'; sleep 5; done;
cd ~/wordpress_data/wp-content/plugins
git clone -b ${TRAVIS_BRANCH} --single-branch https://github.com/impress-org/give.git
cd ~/wordpress_data/wp-content/plugins/give/
docker exec give_wordpress_1 wp plugin activate give
composer install
rm -rf ./node_modules package-lock.json
npm cache clean --force
npm install
npm run dev
npm run test
