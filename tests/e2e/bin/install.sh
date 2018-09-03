#!/usr/bin/env bash

echo "Give Log: current branch is ${TRAVIS_BRANCH}";
echo "${TRAVIS_PHP_VERSION:0:3}";
echo "${TRAVIS_EVENT_TYPE}"

if [[ ${TRAVIS_PHP_VERSION:0:3} != "5.3" ]] && [[ ${TRAVIS_BRANCH} == "master" ]]; then
	echo 'Give Log: setup and run frontend tests';

	until $(curl --output /dev/null --silent --head --fail http://localhost:8004); do printf '.'; sleep 5; done;
	cd ~/wordpress_data/wp-content/plugins
	git clone -b ${TRAVIS_BRANCH} --single-branch https://github.com/WordImpress/Give.git
	cd ~/wordpress_data/wp-content/plugins/Give/
	docker exec give_wordpress_1 wp plugin activate Give
	composer install
	npm install
	npm run dev
	npm run test

else
	echo 'Give Log: Stop frontend tests from running on branches other then master';
fi
