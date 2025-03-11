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
    public function testGetDonationsByDate(): void
    {
        $campaign = Campaign::factory()->create();
        $form = DonationForm::find($campaign->defaultFormId);

        $dates = [
            '2024-01-01',
            '2024-01-02',
            '2024-01-03',
            '2025-01-01',
            '2025-01-02',
            '2025-01-03',
            '2025-02-01',
            '2025-02-02',
            '2025-02-03',
            '2025-03-01',
            '2025-03-02',
            '2025-03-03',
        ];

        foreach ($dates as $date) {
            Donation::factory()->create([
                'formId' => $form->id,
                'status' => DonationStatus::COMPLETE(),
                'amount' => new Money(1000, 'USD'),
                'createdAt' => date_create($date . ' 00:00:00'),
            ]);
        }

        $expectedDataForDayQuery = [];
        foreach ($dates as $date) {
            $expectedDataForDayQuery[] = (object)[
                'date_created' => $date,
                'amount' => 10.00,
                'year' => $this->getYear($date),
                'month' => $this->getMonth($date),
                'day' => $this->getDay($date),
            ];
        }

        $expectedDataForMonthQuery = [
            (object)[
                'date_created' => $dates[0],
                'amount' => 30.00,
                'year' => $this->getYear($dates[0]),
                'month' => $this->getMonth($dates[0]),
                'day' => $this->getDay($dates[0]),
            ],
            (object)[
                'date_created' => $dates[3],
                'amount' => 30.00,
                'year' => $this->getYear($dates[3]),
                'month' => $this->getMonth($dates[3]),
                'day' => $this->getDay($dates[3]),
            ],
            (object)[
                'date_created' => $dates[6],
                'amount' => 30.00,
                'year' => $this->getYear($dates[6]),
                'month' => $this->getMonth($dates[6]),
                'day' => $this->getDay($dates[6]),
            ],
            (object)[
                'date_created' => $dates[9],
                'amount' => 30.00,
                'year' => $this->getYear($dates[9]),
                'month' => $this->getMonth($dates[9]),
                'day' => $this->getDay($dates[9]),
            ],
        ];

        $expectedDataForYearQuery = [
            (object)[
                'date_created' => $dates[0],
                'amount' => 30.00,
                'year' => $this->getYear($dates[0]),
                'month' => $this->getMonth($dates[0]),
                'day' => $this->getDay($dates[0]),
            ],
            (object)[
                'date_created' => $dates[3],
                'amount' => 90.00,
                'year' => $this->getYear($dates[3]),
                'month' => $this->getMonth($dates[3]),
                'day' => $this->getDay($dates[3]),
            ],
        ];

        $query = new CampaignDonationQuery($campaign);

        $this->assertEquals($expectedDataForDayQuery, $query->getDonationsByDate('DAY'));
        $this->assertEquals($expectedDataForMonthQuery, $query->getDonationsByDate('MONTH'));
        $this->assertEquals($expectedDataForYearQuery, $query->getDonationsByDate('YEAR'));
        $this->assertEquals($dates[0], $query->getOldestDonationDate());
    }

    /**
     * @unreleased
     */
    protected function getYear($date): string
    {
        return (new DateTime($date))->format('Y');
    }

    /**
     * @unreleased
     */
    protected function getMonth($date): string
    {
        return (new DateTime($date))->format('m');
    }

    /**
     * @unreleased
     */
    protected function getDay($date): string
    {
        return (new DateTime($date))->format('d');
    }
}
