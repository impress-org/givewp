<?php

/*
 * Test configuration for PHPUnit running inside the wp-env cli container (`npm run test:php`).
 *
 * wp-env writes a wp-tests-config.php of its own, but it has one table prefix for every process,
 * and paratest needs one per worker.
 */

define('ABSPATH', '/var/www/html/');

define('WP_DEFAULT_THEME', 'default');

define('WP_DEBUG', true);

/*
 * wp-env's database and its fixed credentials. Written out because paratest workers start without
 * the container's environment.
 */
define('DB_NAME', 'wordpress');
define('DB_USER', 'root');
define('DB_PASSWORD', 'password');
define('DB_HOST', 'mysql');
define('DB_CHARSET', 'utf8');
define('DB_COLLATE', '');

define('AUTH_KEY', 'put your unique phrase here');
define('SECURE_AUTH_KEY', 'put your unique phrase here');
define('LOGGED_IN_KEY', 'put your unique phrase here');
define('NONCE_KEY', 'put your unique phrase here');
define('AUTH_SALT', 'put your unique phrase here');
define('SECURE_AUTH_SALT', 'put your unique phrase here');
define('LOGGED_IN_SALT', 'put your unique phrase here');
define('NONCE_SALT', 'put your unique phrase here');

/* Paratest sets TEST_TOKEN per worker; each worker installs WordPress under its own prefix. */
$table_prefix = getenv('TEST_TOKEN') ? 'wptests' . getenv('TEST_TOKEN') . '_' : 'wptests_';

define('WP_TESTS_DOMAIN', 'example.org');
define('WP_TESTS_EMAIL', 'admin@example.org');
define('WP_TESTS_TITLE', 'Test Blog');

/* Absolute path: paratest workers run without PATH, and the WordPress test bootstrap shells out to this. */
define('WP_PHP_BINARY', PHP_BINARY);

define('WPLANG', '');
