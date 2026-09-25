<?php

namespace Give\Tests\Unit\Campaigns\Actions;

use Give\Campaigns\Actions\CacheCampaignData;
use Give\Campaigns\Models\Campaign;
use Give\Campaigns\Repositories\CampaignsDataRepository;
use Give\Campaigns\ValueObjects\CampaignGoalType;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationMode;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\Support\ValueObjects\Money;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @since TBD
 */
final class CacheCampaignDataTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @since TBD
     */
    public function setUp(): void
    {
        parent::setUp();

        delete_option('give_campaigns_data');
        delete_option('give_campaigns_subscriptions_data');
        add_filter('give_is_test_mode', '__return_false');
    }

    /**
     * @since TBD
     */
    public function tearDown(): void
    {
        remove_filter('give_is_test_mode', '__return_false');
        remove_filter('give_is_test_mode', '__return_true', 20);

        parent::tearDown();
    }

    /**
     * @since TBD
     */
    public function testKeepsZeroRowForCampaignWithNoDonations()
    {
        $campaign = Campaign::factory()->create(['goalType' => CampaignGoalType::AMOUNT()]);
        CampaignsDataRepository::campaigns([$campaign->id]);

        give(CacheCampaignData::class)->handleCache($campaign->id);

        $this->assertEquals(0, CampaignsDataRepository::campaigns([$campaign->id])->getDonationsCount($campaign));
        $this->assertEquals(0, CampaignsDataRepository::campaigns([$campaign->id])->getRevenue($campaign));
    }

    /**
     * @since TBD
     */
    public function testRefreshesLiveStatsWhileTestModeIsEnabled()
    {
        $campaign = Campaign::factory()->create(['goalType' => CampaignGoalType::AMOUNT()]);
        CampaignsDataRepository::campaigns([$campaign->id]);
        Donation::factory()->create([
            'campaignId' => $campaign->id,
            'formId' => $campaign->defaultFormId,
            'status' => DonationStatus::COMPLETE(),
            'mode' => DonationMode::LIVE(),
            'amount' => new Money(1000, 'USD'),
        ]);

        add_filter('give_is_test_mode', '__return_true', 20);
        give(CacheCampaignData::class)->handleCache($campaign->id);
        remove_filter('give_is_test_mode', '__return_true', 20);

        $this->assertEquals(1, CampaignsDataRepository::campaigns([$campaign->id])->getDonationsCount($campaign));
    }
}
