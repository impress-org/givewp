<?php

use Symfony\Component\Finder\Finder;

$srcDir = dirname(__DIR__, 1) . '/src/Framework';

$iterator = Finder::create()
    ->files()
    ->name('*.php')
    ->notName('ServiceProvider.php')
    ->in([
        dirname(__DIR__) . '/src/Framework/QueryBuilder',
        dirname(__DIR__) . '/src/Framework/FieldsAPI',
        dirname(__DIR__) . '/src/Framework/Database',
        dirname(__DIR__) . '/src/Framework/Blocks',
        dirname(__DIR__) . '/src/Framework/FormDesigns',
        dirname(__DIR__) . '/src/Framework/PaymentGateways',
        dirname(__DIR__) . '/src/Framework/Support',
        dirname(__DIR__) . '/src/Donations/Models',
        dirname(__DIR__) . '/src/Donations/Properties',
        dirname(__DIR__) . '/src/Donations/Repositories',
        dirname(__DIR__) . '/src/Donations/ValueObjects',
        dirname(__DIR__) . '/src/DonationForms/Models',
        dirname(__DIR__) . '/src/DonationForms/Properties',
        dirname(__DIR__) . '/src/DonationForms/Repositories',
        dirname(__DIR__) . '/src/Donors/Models',
        dirname(__DIR__) . '/src/Donors/Repositories',
        dirname(__DIR__) . '/src/Donors/ValueObjects',
        dirname(__DIR__) . '/src/Subscriptions/Models',
        dirname(__DIR__) . '/src/Subscriptions/Repositories',
        dirname(__DIR__) . '/src/Subscriptions/ValueObjects'
    ])
    ->exclude('Migrations');

/**
 * @unreleased
 *
 * Public URL: /wp-content/plugins/give/.docs/build/index.html
 */
return new Doctum\Doctum($iterator, [
    'title' => 'GiveWP',
    'language' => 'en',
    'build_dir' => __DIR__ . '/build',
    'cache_dir' => __DIR__ . '/cache',
    //'source_dir'           => $srcDir,
    //'remote_repository'    => new GitHubRemoteRepository('username/repository', '/path/to/repository'),
    //'default_opened_level' => 2, // optional, 2 is the default value
]);