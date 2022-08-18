# Give Unit Tests [![Build Status](https://api.travis-ci.org/impress-org/give.png?branch=master)](https://api.travis-ci.org/impress-org/give)

This folder contains instructions and test code for Give WordPress unit testing with PHPUnit.

## Initial Setup

1) Install [PHPUnit](http://phpunit.de/) by following their [installation guide](https://phpunit.de/getting-started.html). If you've installed it correctly, this should display the version:

    `$ phpunit --version`

    Note: WordPress requires specific version constraints for PHPUnit ( 5.4 >= PHPUNIT <= 7.x ). If you have a different version of PHPUnit installed globally then you can run a per-project version of PHPUnit with `/vendor/bin/phpunit`.

## Testing Environment

Your WordPress testing environment can be configured in `tests/wp-tests-config.dist.php`.

If `tests/wp-tests-config.php` does not exist, copy `tests/wp-tests-config.dist.php` as a new file.

If you need to use a socket for your database, then your host will be colon-delimited: `localhost:/path/to/socket`

**Important**: The `<db-name>` database will be created if it doesn't exist and all data will be removed during testing.

## Running Tests

Change directory to the plugin root directory and run:

    $ composer run test

The tests will execute and you'll be presented with a summary.

You can run specific tests using `--filter` followed by the class name and/or method to test:

    $ composer run test -- --filter Tests_Templates

    $ composer run test -- --filter test_get_donation_form

    $ composer run test -- --filter Tests_Templates::test_get_donation_form

## Writing Tests

* Each test method should cover a single method or function with one or more assertions
* A single method or function can have multiple associated test methods if it's a large or complex method
* Prefer `assertsEquals()` where possible as it tests both type & equality
* Only methods prefixed with `test` will be run so use helper methods liberally to keep test methods small and reduce code duplication.
* Use data providers where possible. Read more about [data providers](https://phpunit.de/manual/current/en/writing-tests-for-phpunit.html#writing-tests-for-phpunit.data-providers).
* Filters persist between test cases so be sure to remove them in your test method or in the `tearDown()` method.

For more information on how to write PHPUnit Tests, see [PHPUnit's Website](http://www.phpunit.de/manual/3.6/en/writing-tests-for-phpunit.html).

## Automated Tests

Tests are automatically run via [Github Actions](https://github.com/impress-org/givewp/actions) for each commit and pull request.
