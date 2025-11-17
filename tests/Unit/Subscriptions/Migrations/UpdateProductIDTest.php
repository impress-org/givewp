<?php

namespace Give\Tests\Unit\Subscriptions\Migrations;

use Exception;
use Give\Subscriptions\Migrations\UpdateProductID;
use Give\Subscriptions\Models\Subscription;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @since 4.12.0
 *
 * @covers \Give\Subscriptions\Migrations\UpdateProductID
 */
class UpdateProductIDTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @since 4.12.0
     *
     * @throws Exception
     */
    public function testMigrationUpdatesProductId()
    {
        $subscription = Subscription::factory()->createWithDonation([
            'donationFormId' => 100,
        ], [
            'formId' => 200,
        ]);

        (new UpdateProductID())->run();

        $updatedSubscription = Subscription::find($subscription->id);

        $this->assertEquals(200, $updatedSubscription->donationFormId);
    }
}
