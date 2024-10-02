<?php

namespace Give\Tests\Unit\Campaigns\Routes;

use DateTime;
use Give\Campaigns\Models\Campaign;
use Give\Campaigns\Routes\GetCampaignStatistics;
use Give\DonationForms\Models\DonationForm;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\Database\DB;
use Give\Framework\Support\ValueObjects\Money;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use WP_REST_Request;

/**
 * @unreleased
 */
final class CampaignOverviewStatisticsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @unreleased
     */
    public function testReturnsAllTimeDonationsStatistics()
    {
        $campaign = Campaign::factory()->create();
        $form = DonationForm::factory()->create();

        $db = DB::table('give_campaign_forms');
        $db->insert(['form_id' => $form->id, 'campaign_id' => $campaign->id]);

        $donation1 = Donation::factory()->create([
            'formId' => $form->id,
            'status' => DonationStatus::COMPLETE(),
            'amount' => new Money(1000, 'USD'),
            'createdAt' => new DateTime('-35 days'),
        ]);
        give_update_meta($donation1->id, '_give_completed_date', $donation1->createdAt->format('Y-m-d H:i:s'));

        $donation2 = Donation::factory()->create([
            'formId' => $form->id,
            'status' => DonationStatus::COMPLETE(),
            'amount' => new Money(1000, 'USD'),
            'createdAt' => new DateTime('-5 days'),
        ]);
        give_update_meta($donation2->id, '_give_completed_date', $donation2->createdAt->format('Y-m-d H:i:s'));

        $donation3 = Donation::factory()->create([
            'formId' => $form->id,
            'status' => DonationStatus::COMPLETE(),
            'amount' => new Money(1000, 'USD'),
            'createdAt' => new DateTime('now'),
        ]);
        give_update_meta($donation3->id, '_give_completed_date', $donation3->createdAt->format('Y-m-d H:i:s'));

        $request = new WP_REST_Request('GET', '/give-api/v2/campaign-overview-statistics');
        $request->set_param('campaignId', $campaign->id);

        $route = new GetCampaignStatistics;
        $response = $route->handleRequest($request);

        $this->assertEquals(3, $response->data[0]['donorCount']);
        $this->assertEquals(3, $response->data[0]['donationCount']);
        $this->assertEquals(30, $response->data[0]['amountRaised']);
    }

    /**
     * @unreleased
     */
    public function testReturnsPeriodStatisticsWithPreviousPeriod()
    {
        $campaign = Campaign::factory()->create();
        $form = DonationForm::factory()->create();

        $db = DB::table('give_campaign_forms');
        $db->insert(['form_id' => $form->id, 'campaign_id' => $campaign->id]);

        $donation1 = Donation::factory()->create([
            'formId' => $form->id,
            'status' => DonationStatus::COMPLETE(),
            'amount' => new Money(1000, 'USD'),
            'createdAt' => new DateTime('-35 days'),
        ]);
        give_update_meta($donation1->id, '_give_completed_date', $donation1->createdAt->format('Y-m-d H:i:s'));

        $donation2 = Donation::factory()->create([
            'formId' => $form->id,
            'status' => DonationStatus::COMPLETE(),
            'amount' => new Money(1000, 'USD'),
            'createdAt' => new DateTime('-5 days'),
        ]);
        give_update_meta($donation2->id, '_give_completed_date', $donation2->createdAt->format('Y-m-d H:i:s'));

        $donation3 = Donation::factory()->create([
            'formId' => $form->id,
            'status' => DonationStatus::COMPLETE(),
            'amount' => new Money(1000, 'USD'),
            'createdAt' => new DateTime('now'),
        ]);
        give_update_meta($donation3->id, '_give_completed_date', $donation3->createdAt->format('Y-m-d H:i:s'));

        $request = new WP_REST_Request('GET', '/give-api/v2/campaign-overview-statistics');
        $request->set_param('campaignId', $campaign->id);
        $request->set_param('rangeInDays', 30);

        $route = new GetCampaignStatistics;
        $response = $route->handleRequest($request);

        $this->assertEquals(2, $response->data[0]['donorCount']);
        $this->assertEquals(2, $response->data[0]['donationCount']);
        $this->assertEquals(20, $response->data[0]['amountRaised']);

        $this->assertEquals(1, $response->data[1]['donorCount']);
        $this->assertEquals(1, $response->data[1]['donationCount']);
        $this->assertEquals(10, $response->data[1]['amountRaised']);
    }
}
