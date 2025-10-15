<?php

namespace Give\Tests\Unit\Subscriptions\Migrations;

use Exception;
use Give\Subscriptions\Migrations\UpdateProductID;
use Give\Subscriptions\Models\Subscription;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @unreleased
 *
 * @covers \Give\Subscriptions\Migrations\UpdateProductID
 */
class UpdateProductIDTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @unreleased
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

        $this->assertEquals($subscription->donationFormId, $updatedSubscription->donationFormId);
    }
}
