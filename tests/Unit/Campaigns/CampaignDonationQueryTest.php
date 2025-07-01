<?php

namespace Give\Tests\Unit\Campaigns;

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
 * @since 4.0.0
 */
final class CampaignDonationQueryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @since 4.0.0
     */
    public function testCountCampaignDonations()
    {
        /** @var Campaign $campaign */
        $campaign = Campaign::factory()->create();
        $form = DonationForm::find($campaign->defaultFormId);


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
     * @since 4.0.0
     */
    public function testSumIntendedAmountReturnsSumOfDonationsWithoutRecoveredFees()
    {
        $campaign = Campaign::factory()->create();
        $form = DonationForm::find($campaign->defaultFormId);

        Donation::factory()->create([
            'formId' => $form->id,
            'status' => DonationStatus::COMPLETE(),
            'amount' => new Money(1051, 'USD'),
            'feeAmountRecovered' => new Money(35, 'USD'),
        ]);

        Donation::factory()->create([
            'formId' => $form->id,
            'status' => DonationStatus::COMPLETE(),
            'amount' => new Money(1051, 'USD'),
            'feeAmountRecovered' => new Money(35, 'USD'),
        ]);

        $query = new CampaignDonationQuery($campaign);

        $this->assertEquals(20.32, $query->sumIntendedAmount());
    }

    /**
     * @since 4.0.0
     */
    public function testCountCampaignDonors()
    {
        $campaign = Campaign::factory()->create();
        $form = DonationForm::find($campaign->defaultFormId);

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
     * @since 4.0.0
     */
    public function testSumIntendedAmountWithoutRecoveredFees()
    {
        $campaign = Campaign::factory()->create();
        $form = DonationForm::find($campaign->defaultFormId);

        $donation = Donation::factory()->create([
            'formId' => $form->id,
            'status' => DonationStatus::COMPLETE(),
            'amount' => new Money(1070, 'USD'),
            'feeAmountRecovered' => new Money(70, 'USD'),
        ]);

        $query = new CampaignDonationQuery($campaign);

        $this->assertEquals(10.00, $query->sumIntendedAmount());
    }

    /**
     * @unreleased
     */
    public function testSumIntendedAmountWithExchangeRates()
    {
        $campaign = Campaign::factory()->create();
        $form = DonationForm::find($campaign->defaultFormId);

        // Donation in EUR with 1.2 exchange rate (1 USD = 1.2 EUR)
        // €120 / 1.2 = $100 USD
        Donation::factory()->create([
            'formId' => $form->id,
            'status' => DonationStatus::COMPLETE(),
            'amount' => new Money(12000, 'EUR'), // €120.00
            'feeAmountRecovered' => new Money(0, 'EUR'),
            'exchangeRate' => '1.2',
        ]);

        // Donation in GBP with 0.8 exchange rate (1 USD = 0.8 GBP)
        // £80 / 0.8 = $100 USD
        Donation::factory()->create([
            'formId' => $form->id,
            'status' => DonationStatus::COMPLETE(),
            'amount' => new Money(8000, 'GBP'), // £80.00
            'feeAmountRecovered' => new Money(0, 'GBP'),
            'exchangeRate' => '0.8',
        ]);

        // Donation in USD with default exchange rate (no conversion)
        // $50 / 1 = $50 USD
        Donation::factory()->create([
            'formId' => $form->id,
            'status' => DonationStatus::COMPLETE(),
            'amount' => new Money(5000, 'USD'), // $50.00
            'feeAmountRecovered' => new Money(0, 'USD'),
            'exchangeRate' => '1',
        ]);

        $query = new CampaignDonationQuery($campaign);

        // Total should be: $100 + $100 + $50 = $250
        $this->assertEquals(250.00, $query->sumIntendedAmount());
    }

    /**
     * @unreleased
     */
    public function testSumIntendedAmountWithExchangeRatesAndFees()
    {
        $campaign = Campaign::factory()->create();
        $form = DonationForm::find($campaign->defaultFormId);

        // Donation in EUR: (€120 - €20 fee) / 1.25 = €100 / 1.25 = $80.00 USD
        Donation::factory()->create([
            'formId' => $form->id,
            'status' => DonationStatus::COMPLETE(),
            'amount' => new Money(12000, 'EUR'), // €120.00
            'feeAmountRecovered' => new Money(2000, 'EUR'), // €20.00
            'exchangeRate' => '1.25',
        ]);

        // Donation in GBP: (£80 - £16 fee) / 0.8 = £64 / 0.8 = $80.00 USD
        Donation::factory()->create([
            'formId' => $form->id,
            'status' => DonationStatus::COMPLETE(),
            'amount' => new Money(8000, 'GBP'), // £80.00
            'feeAmountRecovered' => new Money(1600, 'GBP'), // £16.00
            'exchangeRate' => '0.8',
        ]);

        $query = new CampaignDonationQuery($campaign);

        // Total should be: $80.00 + $80.00 = $160.00
        $this->assertEquals(160.00, $query->sumIntendedAmount());
    }

    /**
     * @since 4.0.0
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
    public function testGetDonationsByDateWithExchangeRates(): void
    {
        $campaign = Campaign::factory()->create();
        $form = DonationForm::find($campaign->defaultFormId);

        // Create donations with different exchange rates on the same date
        $testDate = '2024-01-01';

        // Donation in EUR: €120 / 1.2 = $100 USD
        Donation::factory()->create([
            'formId' => $form->id,
            'status' => DonationStatus::COMPLETE(),
            'amount' => new Money(12000, 'EUR'),
            'feeAmountRecovered' => new Money(0, 'EUR'),
            'exchangeRate' => '1.2',
            'createdAt' => date_create($testDate . ' 00:00:00'),
        ]);

        // Donation in GBP: £80 / 0.8 = $100 USD
        Donation::factory()->create([
            'formId' => $form->id,
            'status' => DonationStatus::COMPLETE(),
            'amount' => new Money(8000, 'GBP'),
            'feeAmountRecovered' => new Money(0, 'GBP'),
            'exchangeRate' => '0.8',
            'createdAt' => date_create($testDate . ' 00:00:00'),
        ]);

        // Donation in USD: $50 / 1 = $50 USD
        Donation::factory()->create([
            'formId' => $form->id,
            'status' => DonationStatus::COMPLETE(),
            'amount' => new Money(5000, 'USD'),
            'feeAmountRecovered' => new Money(0, 'USD'),
            'exchangeRate' => '1',
            'createdAt' => date_create($testDate . ' 00:00:00'),
        ]);

        $expectedDataForDayQuery = [
            (object)[
                'date_created' => $testDate,
                'amount' => 250.00, // $100 + $100 + $50 = $250
                'year' => $this->getYear($testDate),
                'month' => $this->getMonth($testDate),
                'day' => $this->getDay($testDate),
            ]
        ];

        $query = new CampaignDonationQuery($campaign);

        $this->assertEquals($expectedDataForDayQuery, $query->getDonationsByDate('DAY'));
    }

    /**
     * @since 4.0.0
     */
    protected function getYear(string $date): string
    {
        return date_create($date)->format('Y');
    }

    /**
     * @since 4.0.0
     */
    protected function getMonth(string $date): string
    {
        // MySQL returns without leading zero
        return date_create($date)->format('n');
    }

    /**
     * @since 4.0.0
     */
    protected function getDay(string $date): string
    {
        // MySQL returns without leading zero
        return date_create($date)->format('j');
    }
}
