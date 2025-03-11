<?php

namespace Give\Tests\Unit\Campaigns;

use DateTime;
use Give\Campaigns\CampaignDonationQuery;
use Give\Campaigns\Models\Campaign;
use Give\DonationForms\Models\DonationForm;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\Database\DB;
use Give\Framework\Support\ValueObjects\Money;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @unreleased
 */
final class CampaignDonationQueryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @unreleased
     */
    public function testCountCampaignDonations()
    {
        /** @var Campaign $campaign */
        $campaign = Campaign::factory()->create();
        $form = DonationForm::find($campaign->defaultFormId);

        $db = DB::table('give_campaign_forms');


        Donation::factory()->create([
            'formId' => $form->id,
            'status' => DonationStatus::COMPLETE(),
            'amount' => new Money(1000, 'USD'),
        ]);
        Donation::factory()->create([
            'formId' => $form->id,
            'status' => DonationStatus::COMPLETE(),
            'amount' => new Money(1000, 'USD'),
        ]);

        $query = new CampaignDonationQuery($campaign);

        $this->assertEquals(2, $query->countDonations());
    }

    /**
     * @unreleased
     */
    public function testSumCampaignDonations()
    {
        $campaign = Campaign::factory()->create();
        $form = DonationForm::find($campaign->defaultFormId);

        $db = DB::table('give_campaign_forms');


        Donation::factory()->create([
            'formId' => $form->id,
            'status' => DonationStatus::COMPLETE(),
            'amount' => new Money(1000, 'USD'),
        ]);
        Donation::factory()->create([
            'formId' => $form->id,
            'status' => DonationStatus::COMPLETE(),
            'amount' => new Money(1000, 'USD'),
        ]);

        $query = new CampaignDonationQuery($campaign);

        $this->assertEquals(20.00, $query->sumIntendedAmount());
    }

    /**
     * @unreleased
     */
    public function testCountCampaignDonors()
    {
        $campaign = Campaign::factory()->create();
        $form = DonationForm::find($campaign->defaultFormId);

        $db = DB::table('give_campaign_forms');

        Donation::factory()->create([
            'formId' => $form->id,
            'status' => DonationStatus::COMPLETE(),
            'amount' => new Money(1000, 'USD'),
        ]);
        Donation::factory()->create([
            'formId' => $form->id,
            'status' => DonationStatus::COMPLETE(),
            'amount' => new Money(1000, 'USD'),
        ]);

        $query = new CampaignDonationQuery($campaign);

        $this->assertEquals(2, $query->countDonors());
    }

    /**
     * @unreleased
     */
    public function testCoalesceIntendedAmountWithoutRecoveredFees()
    {
        $campaign = Campaign::factory()->create();
        $form = DonationForm::find($campaign->defaultFormId);

        $db = DB::table('give_campaign_forms');


        $donation = Donation::factory()->create([
            'formId' => $form->id,
            'status' => DonationStatus::COMPLETE(),
            'amount' => new Money(1070, 'USD'),
        ]);
        give_update_meta($donation->id, '_give_fee_donation_amount', 10.00);

        $query = new CampaignDonationQuery($campaign);

        $this->assertEquals(10.00, $query->sumIntendedAmount());
    }

    /**
     * @unreleased
     */
    public function testGetDonationsByDate()
    {
        $campaign = Campaign::factory()->create();
        $form = DonationForm::find($campaign->defaultFormId);

        $donations = [
            Donation::factory()->create([
                'formId' => $form->id,
                'status' => DonationStatus::COMPLETE(),
                'amount' => new Money(1000, 'USD'),
                'createdAt' => new DateTime('2021-01-01 00:00:00'),
            ]),
            Donation::factory()->create([
                'formId' => $form->id,
                'status' => DonationStatus::COMPLETE(),
                'amount' => new Money(1000, 'USD'),
                'createdAt' => new DateTime('2021-01-02 00:00:00'),
            ]),
            Donation::factory()->create([
                'formId' => $form->id,
                'status' => DonationStatus::COMPLETE(),
                'amount' => new Money(1000, 'USD'),
                'createdAt' => new DateTime('2021-01-02 00:00:00'),
            ]),
        ];

        $query = new CampaignDonationQuery($campaign);

        $this->assertEquals([
            (object) ['date' => '2021-01-01', 'amount' => 10.00],
            (object) ['date' => '2021-01-02', 'amount' => 20.00],
        ], $query->getDonationsByDay());
    }
}
