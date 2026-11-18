<?php

namespace Give\Tests\Unit\Campaigns;

use Give\Campaigns\Models\Campaign;
use Give\Campaigns\Repositories\CampaignsDataRepository;
use Give\Campaigns\ValueObjects\CampaignGoalType;
use Give\DonationForms\Models\DonationForm;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationMode;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Donors\Models\Donor;
use Give\Framework\Support\ValueObjects\Money;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @since 4.2.0
 */
final class CampaignsDataRepositoryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The cache lives in options, which RefreshDatabase does not truncate, and a donation insert
     * commits the test transaction. Start every test with a cold cache, in live mode so the
     * repository uses the cache at all. The mode is set through the filter rather than the
     * setting, because give_update_option() leaves its global stale when the stored value
     * already matches.
     *
     * @since 4.18.0
     */
    public function setUp(): void
    {
        parent::setUp();

        delete_option('give_campaigns_data');
        delete_option('give_campaigns_subscriptions_data');
        add_filter('give_is_test_mode', '__return_false');
    }

    /**
     * @since 4.18.0
     */
    public function tearDown(): void
    {
        remove_filter('give_is_test_mode', '__return_false');
        remove_filter('give_is_test_mode', '__return_true', 20);

        parent::tearDown();
    }

    /**
     * @since 4.18.0
     */
    private function enableTestMode(): void
    {
        add_filter('give_is_test_mode', '__return_true', 20);
    }

    /**
     * @since 4.2.0
     */
    public function testCountCampaignDonations()
    {
        /** @var Campaign $campaign */
        $campaign = Campaign::factory()->create([
            'goalType' => CampaignGoalType::AMOUNT(),
        ]);

        $form = DonationForm::find($campaign->defaultFormId);

        Donation::factory()->create([
            'campaignId' => $campaign->id,
            'formId' => $form->id,
            'status' => DonationStatus::COMPLETE(),
            'mode' => DonationMode::LIVE(),
            'amount' => new Money(1000, 'USD'),
        ]);
        Donation::factory()->create([
            'campaignId' => $campaign->id,
            'formId' => $form->id,
            'status' => DonationStatus::COMPLETE(),
            'mode' => DonationMode::LIVE(),
            'amount' => new Money(1000, 'USD'),
        ]);

        $campaignsData = CampaignsDataRepository::campaigns([$campaign->id]);

        $this->assertEquals(2, $campaignsData->getDonationsCount($campaign));
    }

    /**
     * @since 4.2.0
     */
    public function testGetRevenueReturnsSumOfDonationsWithoutRecoveredFees()
    {
        /** @var Campaign $campaign */
        $campaign = Campaign::factory()->create([
            'goalType' => CampaignGoalType::AMOUNT(),
        ]);

        $form = DonationForm::find($campaign->defaultFormId);

        Donation::factory()->create([
            'formId' => $form->id,
            'status' => DonationStatus::COMPLETE(),
            'mode' => DonationMode::LIVE(),
            'amount' => new Money(1051, 'USD'),
            'feeAmountRecovered' => new Money(35, 'USD'),
        ]);
        Donation::factory()->create([
            'formId' => $form->id,
            'status' => DonationStatus::COMPLETE(),
            'mode' => DonationMode::LIVE(),
            'amount' => new Money(1051, 'USD'),
            'feeAmountRecovered' => new Money(35, 'USD'),
        ]);

        $campaignsData = CampaignsDataRepository::campaigns([$campaign->id]);

        $this->assertEquals(20.32, $campaignsData->getRevenue($campaign));
    }

    /**
     * @since 4.18.0
     */
    public function testWarmCacheStillReturnsStatsForCampaignsMissingFromIt()
    {
        $cachedCampaign = Campaign::factory()->create(['goalType' => CampaignGoalType::AMOUNT()]);
        CampaignsDataRepository::campaigns([$cachedCampaign->id]);

        $newCampaign = Campaign::factory()->create(['goalType' => CampaignGoalType::AMOUNT()]);
        Donation::factory()->create([
            'campaignId' => $newCampaign->id,
            'formId' => $newCampaign->defaultFormId,
            'status' => DonationStatus::COMPLETE(),
            'mode' => DonationMode::LIVE(),
            'amount' => new Money(1000, 'USD'),
        ]);

        $campaignsData = CampaignsDataRepository::campaigns([$cachedCampaign->id, $newCampaign->id]);

        $this->assertEquals(1, $campaignsData->getDonationsCount($newCampaign));
        $this->assertEquals(10, $campaignsData->getRevenue($newCampaign));
        $this->assertContains(
            (string)$newCampaign->id,
            array_map('strval', array_column(get_option('give_campaigns_data')['donationsCount'], 'campaign_id'))
        );
    }

    /**
     * @since 4.18.0
     */
    public function testCampaignWithNoDonationsIsCachedAsZero()
    {
        $campaign = Campaign::factory()->create(['goalType' => CampaignGoalType::AMOUNT()]);

        $campaignsData = CampaignsDataRepository::campaigns([$campaign->id]);

        $this->assertEquals(0, $campaignsData->getDonationsCount($campaign));
        $this->assertContains(
            (string)$campaign->id,
            array_map('strval', array_column(get_option('give_campaigns_data')['donationsCount'], 'campaign_id'))
        );
    }

    /**
     * @since 4.18.0
     */
    public function testTestModeReadsTestDonationsInsteadOfTheLiveCache()
    {
        $campaign = Campaign::factory()->create(['goalType' => CampaignGoalType::AMOUNT()]);
        Donation::factory()->create([
            'campaignId' => $campaign->id,
            'formId' => $campaign->defaultFormId,
            'status' => DonationStatus::COMPLETE(),
            'mode' => DonationMode::LIVE(),
            'amount' => new Money(1000, 'USD'),
        ]);
        CampaignsDataRepository::campaigns([$campaign->id]);
        $liveCache = get_option('give_campaigns_data');

        $this->enableTestMode();
        Donation::factory()->count(2)->create([
            'campaignId' => $campaign->id,
            'formId' => $campaign->defaultFormId,
            'status' => DonationStatus::COMPLETE(),
            'mode' => DonationMode::TEST(),
            'amount' => new Money(1000, 'USD'),
        ]);

        $campaignsData = CampaignsDataRepository::campaigns([$campaign->id]);

        $this->assertEquals(2, $campaignsData->getDonationsCount($campaign));
        $this->assertEquals($liveCache, get_option('give_campaigns_data'));
    }

    /**
     * @since 4.18.0
     */
    public function testTestModeDoesNotWriteTheCache()
    {
        $this->enableTestMode();
        $campaign = Campaign::factory()->create(['goalType' => CampaignGoalType::AMOUNT()]);

        CampaignsDataRepository::campaigns([$campaign->id]);

        $this->assertFalse(get_option('give_campaigns_data'));
    }

    /**
     * @since TBD
     */
    public function testFillingMissingCampaignsDoesNotUndoARefreshThatFinishedMeanwhile()
    {
        $cached = Campaign::factory()->create(['goalType' => CampaignGoalType::AMOUNT()]);
        $missing = Campaign::factory()->create(['goalType' => CampaignGoalType::AMOUNT()]);
        update_option('give_campaigns_data', [
            'amounts' => [['campaign_id' => (string)$cached->id, 'sum' => 0]],
            'donationsCount' => [['campaign_id' => (string)$cached->id, 'count' => 0]],
            'donorsCount' => [['campaign_id' => (string)$cached->id, 'count' => 0]],
        ]);

        // A refresh stores new numbers for the cached campaign after the fill read the option and
        // while the missing campaign is queried, so before the fill's write.
        $done = false;
        $refresh = function ($query) use (&$done, $cached) {
            if (!$done && stripos($query, 'give_donationmeta') !== false && stripos($query, 'SELECT') === 0) {
                $done = true;
                update_option('give_campaigns_data', [
                    'amounts' => [['campaign_id' => (string)$cached->id, 'sum' => 70]],
                    'donationsCount' => [['campaign_id' => (string)$cached->id, 'count' => 7]],
                    'donorsCount' => [['campaign_id' => (string)$cached->id, 'count' => 3]],
                ]);
            }

            return $query;
        };

        add_filter('query', $refresh, 1);
        $campaignsData = CampaignsDataRepository::campaigns([$cached->id, $missing->id]);
        remove_filter('query', $refresh, 1);

        $this->assertTrue($done, 'the refresh ran while the missing campaign was queried');
        $this->assertEquals(7, $campaignsData->getDonationsCount($cached));
        $this->assertEquals(
            ['7', '0'],
            array_map('strval', array_column(get_option('give_campaigns_data')['donationsCount'], 'count'))
        );
    }

    /**
     * @since TBD
     */
    public function testGetRowsHoldsOnlyTheRequestedCampaigns()
    {
        $first = Campaign::factory()->create(['goalType' => CampaignGoalType::AMOUNT()]);
        $second = Campaign::factory()->create(['goalType' => CampaignGoalType::AMOUNT()]);
        CampaignsDataRepository::campaigns([$first->id, $second->id]);

        $rows = CampaignsDataRepository::campaigns([$second->id])->getRows();

        $this->assertSame([(string)$second->id], array_column($rows['donationsCount'], 'campaign_id'));
        $this->assertSame([(string)$second->id], array_column($rows['amounts'], 'campaign_id'));
        $this->assertCount(2, get_option('give_campaigns_data')['donationsCount'], 'the stored option keeps both');
    }

    /**
     * @since 4.2.0
     */
    public function testCountCampaignDonors()
    {
        /** @var Campaign $campaign */
        $campaign = Campaign::factory()->create([
            'goalType' => CampaignGoalType::AMOUNT(),
        ]);

        $form = DonationForm::find($campaign->defaultFormId);

        $donor = Donor::factory()->create();
        $donor2 = Donor::factory()->create();

        Donation::factory()->create([
            'formId' => $form->id,
            'status' => DonationStatus::COMPLETE(),
            'mode' => DonationMode::LIVE(),
            'amount' => new Money(1000, 'USD'),
            'donorId' => $donor->id,
        ]);

        Donation::factory()->create([
            'formId' => $form->id,
            'status' => DonationStatus::COMPLETE(),
            'mode' => DonationMode::LIVE(),
            'amount' => new Money(1000, 'USD'),
            'donorId' => $donor2->id,
        ]);

        $campaignsData = CampaignsDataRepository::campaigns([$campaign->id]);

        $this->assertEquals(2, $campaignsData->getDonorsCount($campaign));
    }
}
