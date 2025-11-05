<?php

namespace Unit\API\REST\V3\Routes\Campaigns;

use DateTime;
use Give\Campaigns\Models\Campaign;
use Give\DonationForms\Models\DonationForm;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\Database\DB;
use Give\Framework\Support\ValueObjects\Money;
use Give\Tests\RestApiTestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give\Tests\TestTraits\HasDefaultWordPressUsers;

/**
 * @unreleased updated to use REST API test case
 * @since 4.0.0
 */
final class GetCampaignStatisticsTest extends RestApiTestCase
{
    use RefreshDatabase;
    use HasDefaultWordPressUsers;

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

        $request = $this->createRequest('GET', "/givewp/v3/campaigns/$campaign->id/statistics", [], 'administrator');
        $request->set_param('id', $campaign->id);
        $response = $this->dispatchRequest($request);

        $data = $response->get_data();
        $this->assertEquals(3, $data[0]['donorCount']);
        $this->assertEquals(3, $data[0]['donationCount']);
        $this->assertEquals(30, $data[0]['amountRaised']);
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

        $request = $this->createRequest('GET', "/givewp/v3/campaigns/$campaign->id/statistics", [], 'administrator');
        $request->set_param('id', $campaign->id);
        $request->set_param('rangeInDays', 30);
        $response = $this->dispatchRequest($request);

        $data = $response->get_data();
        $this->assertEquals(2, $data[0]['donorCount']);
        $this->assertEquals(2, $data[0]['donationCount']);
        $this->assertEquals(20, $data[0]['amountRaised']);

        $this->assertEquals(1, $data[1]['donorCount']);
        $this->assertEquals(1, $data[1]['donationCount']);
        $this->assertEquals(10, $data[1]['amountRaised']);
    }
}
