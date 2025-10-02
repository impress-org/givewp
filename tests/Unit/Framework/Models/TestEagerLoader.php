<?php

namespace Give\Tests\Unit\Framework\Models;

use Give\Donors\Models\Donor;
use Give\Framework\Models\EagerLoader;
use Give\Subscriptions\Models\Subscription;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @unreleased Change the expected query count to support temporary campaign relationships. This must be reverted once subscriptions implement the campaign id column.
 * @since 3.5.0
 */
class TestEagerLoader extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
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
         * 7 queries are expected:
         * 1. To get the donors
         * 2. To get the donors additional emails
         * 3. To get the donors addresses
         * 4. To get the first donor's subscriptions
         * 5. To populate first donor's subscription campaign relationship
         * 6. To get the second donor's subscriptions
         * 7. To populate second donor's subscription campaign relationship
         */
        $this->assertQueryCount(7, function() {
            foreach(Donor::query()->getAll() as $donor) {
                $donor->subscriptions;
            }
        });

        /**
         * 6 queries are expected:
         * 1. To get the donors
         * 2. To get the donors additional emails
         * 3. To get the donors addresses
         * 4. To get the subscriptions
         * 5. To populate the first donor's subscription campaign relationship
         * 6. To populate the second donor's subscription campaign relationship
         */
        $this->assertQueryCount(6, function() {
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
