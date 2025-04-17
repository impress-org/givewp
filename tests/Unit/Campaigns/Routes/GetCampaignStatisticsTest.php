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
 * @since 4.0.0
 */
final class GetCampaignStatisticsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @since 4.0.0
     */
    public function testReturnsAllTimeDonationsStatistics()
    {
        $campaign = Campaign::factory()->create();
        $form = DonationForm::find($campaign->defaultFormId);

        $db = DB::table('give_campaign_forms');


        $donation1 = Donation::factory()->create([
            'formId' => $form->id,
            'status' => DonationStatus::COMPLETE(),
            'amount' => new Money(1000, 'USD'),
            'createdAt' => new DateTime('-35 days'),
        ]);

        $donation2 = Donation::factory()->create([
            'formId' => $form->id,
            'status' => DonationStatus::COMPLETE(),
            'amount' => new Money(1000, 'USD'),
            'createdAt' => new DateTime('-5 days'),
        ]);

        $donation3 = Donation::factory()->create([
            'formId' => $form->id,
            'status' => DonationStatus::COMPLETE(),
            'amount' => new Money(1000, 'USD'),
            'createdAt' => new DateTime('now'),
        ]);

        $request = new WP_REST_Request('GET', "/give-api/v2/campaigns/$campaign->id/statistics");
        $request->set_param('id', $campaign->id);

        $route = new GetCampaignStatistics;
        $response = $route->handleRequest($request);

        $this->assertEquals(3, $response->data[0]['donorCount']);
        $this->assertEquals(3, $response->data[0]['donationCount']);
        $this->assertEquals(30, $response->data[0]['amountRaised']);
    }

    /**
     * @since 4.0.0
     */
    public function testReturnsPeriodStatisticsWithPreviousPeriod()
    {
        $campaign = Campaign::factory()->create();
        $form = DonationForm::find($campaign->defaultFormId);

        $db = DB::table('give_campaign_forms');


        $donation1 = Donation::factory()->create([
            'formId' => $form->id,
            'status' => DonationStatus::COMPLETE(),
            'amount' => new Money(1000, 'USD'),
            'createdAt' => new DateTime('-35 days'),
        ]);

        $donation2 = Donation::factory()->create([
            'formId' => $form->id,
            'status' => DonationStatus::COMPLETE(),
            'amount' => new Money(1000, 'USD'),
            'createdAt' => new DateTime('-5 days'),
        ]);

        $donation3 = Donation::factory()->create([
            'formId' => $form->id,
            'status' => DonationStatus::COMPLETE(),
            'amount' => new Money(1000, 'USD'),
            'createdAt' => new DateTime('now'),
        ]);

        $request = new WP_REST_Request('GET', "/give-api/v2/campaigns/$campaign->id/statistics");
        $request->set_param('id', $campaign->id);
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
