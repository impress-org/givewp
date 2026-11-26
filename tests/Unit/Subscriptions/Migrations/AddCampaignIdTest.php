<?php

namespace Give\Tests\Unit\Subscriptions\Migrations;

use Exception;
use Give\Framework\Database\DB;
use Give\Subscriptions\Migrations\AddCampaignId;
use Give\Subscriptions\Models\Subscription;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @since 4.20.0
 *
 * @covers \Give\Subscriptions\Migrations\AddCampaignId
 */
class AddCampaignIdTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @since 4.20.0
     *
     * @throws Exception
     */
    public function testMigrationCopiesTheCampaignIdOfTheSubscriptionForm()
    {
        $subscription = Subscription::factory()->create([
            'donationFormId' => 300,
            'campaignId' => 0,
        ]);

        $this->assertSame(0, Subscription::find($subscription->id)->campaignId);

        DB::table('give_campaign_forms')->insert([
            'form_id' => 300,
            'campaign_id' => 77,
        ]);

        (new AddCampaignId())->run();

        $this->assertSame(77, Subscription::find($subscription->id)->campaignId);
    }
}
