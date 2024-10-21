<?php

namespace Give\Tests\Unit\Framework\Models;

use Give\Donors\Models\Donor;
use Give\Framework\Models\EagerLoader;
use Give\Subscriptions\Models\Subscription;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @since 3.5.0
 */
class TestEagerLoader extends TestCase
{
    use RefreshDatabase;

    public function setUp()
    {
        define('SAVEQUERIES', true);
        parent::setUp();
    }

    public function testRelatedModelsAreEagerLoaded()
    {
        $this->refreshDatabase(); // Clear out any existing donors.

        Subscription::factory()->create(); // Also creates an associated donor.
        Subscription::factory()->create(); // Also creates an associated donor.

        /**
         * 4 queries are expected:
         * 1. To get the donors
         * 2. To get the donors additional emails
         * 3. To get the first donor's subscriptions
         * 4. To get the second donor's subscriptions
         */
        $this->assertQueryCount(4, function() {
            foreach(Donor::query()->getAll() as $donor) {
                $donor->subscriptions;
            }
        });

        /**
         * 3 queries are expected:
         * 1. To get the donors
         * 2. To get the donors additional emails
         * 3. To get the subscriptions
         */
        $this->assertQueryCount(3, function() {
            $eagerLoaderQuery = new EagerLoader(Donor::class, Subscription::class, 'subscriptions', 'customer_id', 'donorId');
            $eagerLoaderQuery->getAll();
        });
    }

    protected function assertQueryCount($expected, $callback)
    {
        global $wpdb;
        $wpdb->queries = []; // Reset tracked queries.
        $callback();
        $this->assertEquals($expected, count($wpdb->queries));
    }
}
