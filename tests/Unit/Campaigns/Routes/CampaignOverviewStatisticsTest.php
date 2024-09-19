<?php

namespace Give\Tests\Unit\Campaigns\Routes;

use DateTime;
use Give\Campaigns\Models\Campaign;
use Give\Campaigns\Routes\CampaignOverviewStatistics;
use Give\DonationForms\Models\DonationForm;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\Database\DB;
use Give\Framework\Support\ValueObjects\Money;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use WP_REST_Request;

final class CampaignOverviewStatisticsTest extends TestCase
{
    use RefreshDatabase;

    public function testReturnsAllTimeDonationsStatistics()
    {
        $campaign = Campaign::factory()->create();
        $form = DonationForm::factory()->create();

        $db = DB::table('give_campaign_forms');
        $db->insert(['form_id' => $form->id, 'campaign_id' => $campaign->id]);

        Donation::factory()->create([
            'formId' => $form->id,
            'status' => DonationStatus::COMPLETE(),
            'amount' => new Money(1000, 'USD'),
            'createdAt' => new DateTime('-35 days'),
        ]);
        Donation::factory()->create([
            'formId' => $form->id,
            'status' => DonationStatus::COMPLETE(),
            'amount' => new Money(1000, 'USD'),
            'createdAt' => new DateTime('-5 days'),
        ]);
        Donation::factory()->create([
            'formId' => $form->id,
            'status' => DonationStatus::COMPLETE(),
            'amount' => new Money(1000, 'USD'),
            'createdAt' => new DateTime('now'),
        ]);

        $request = new WP_REST_Request('GET', '/give-api/v2/campaign-overview-statistics');
        $request->set_param('campaignId', $campaign->id);

        $route = new CampaignOverviewStatistics;
        $response = $route->handleRequest($request);

        $this->assertEquals(3, $response[0]['donorCount']);
        $this->assertEquals(3, $response[0]['donationCount']);
        $this->assertEquals(30, $response[0]['amountRaised']);
    }

    public function testReturnsPeriodStatisticsWithPreviousPeriod()
    {
        $campaign = Campaign::factory()->create();
        $form = DonationForm::factory()->create();

        $db = DB::table('give_campaign_forms');
        $db->insert(['form_id' => $form->id, 'campaign_id' => $campaign->id]);

        Donation::factory()->create([
            'formId' => $form->id,
            'status' => DonationStatus::COMPLETE(),
            'amount' => new Money(1000, 'USD'),
            'createdAt' => new DateTime('-35 days'),
        ]);
        Donation::factory()->create([
            'formId' => $form->id,
            'status' => DonationStatus::COMPLETE(),
            'amount' => new Money(1000, 'USD'),
            'createdAt' => new DateTime('-5 days'),
        ]);
        Donation::factory()->create([
            'formId' => $form->id,
            'status' => DonationStatus::COMPLETE(),
            'amount' => new Money(1000, 'USD'),
            'createdAt' => new DateTime('now'),
        ]);

        $request = new WP_REST_Request('GET', '/give-api/v2/campaign-overview-statistics');
        $request->set_param('campaignId', $campaign->id);
        $request->set_param('rangeInDays', 30);

        $route = new CampaignOverviewStatistics;
        $response = $route->handleRequest($request);

        $this->assertEquals(2, $response[0]['donorCount']);
        $this->assertEquals(2, $response[0]['donationCount']);
        $this->assertEquals(20, $response[0]['amountRaised']);

        $this->assertEquals(1, $response[1]['donorCount']);
        $this->assertEquals(1, $response[1]['donationCount']);
        $this->assertEquals(10, $response[1]['amountRaised']);
    }
}
