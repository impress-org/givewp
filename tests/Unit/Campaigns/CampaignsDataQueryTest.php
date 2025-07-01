<?php

namespace Give\Tests\Unit\Campaigns;

use Give\Campaigns\CampaignsDataQuery;
use Give\Campaigns\Models\Campaign;
use Give\Campaigns\ValueObjects\CampaignGoalType;
use Give\DonationForms\Models\DonationForm;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\Support\ValueObjects\Money;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @since 4.2.0
 */
final class CampaignsDataQueryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @since 4.2.0
     */
    public function testCollectInitialAmounts()
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
            'amount' => new Money(1051, 'USD'),
            'feeAmountRecovered' => new Money(35, 'USD'),
        ]);

        Donation::factory()->create([
            'campaignId' => $campaign->id,
            'formId' => $form->id,
            'status' => DonationStatus::COMPLETE(),
            'amount' => new Money(1051, 'USD'),
            'feeAmountRecovered' => new Money(35, 'USD'),
        ]);


        Donation::factory()->create([
            'campaignId' => $campaign->id,
            'formId' => $form->id,
            'status' => DonationStatus::COMPLETE(),
            'amount' => new Money(1000, 'USD'),
        ]);

        $campaignsDataQuery = CampaignsDataQuery::donations([$campaign->id]);

        $this->assertEquals([
            [
                'sum' => 30.32,
                'campaign_id' => $campaign->id,
            ]
        ], $campaignsDataQuery->collectIntendedAmounts());
    }

    /**
     * @unreleased
     */
    public function testCollectIntendedAmountsWithExchangeRates()
    {
        /** @var Campaign $campaign */
        $campaign = Campaign::factory()->create([
            'goalType' => CampaignGoalType::AMOUNT(),
        ]);

        $form = DonationForm::find($campaign->defaultFormId);

        // Donation in EUR: (€120 - €20 fee) / 1.25 = €100 / 1.25 = $80.00 USD
        Donation::factory()->create([
            'campaignId' => $campaign->id,
            'formId' => $form->id,
            'status' => DonationStatus::COMPLETE(),
            'amount' => new Money(12000, 'EUR'), // €120.00
            'feeAmountRecovered' => new Money(2000, 'EUR'), // €20.00
            'exchangeRate' => '1.25',
        ]);

        // Donation in GBP: (£80 - £16 fee) / 0.8 = £64 / 0.8 = $80.00 USD
        Donation::factory()->create([
            'campaignId' => $campaign->id,
            'formId' => $form->id,
            'status' => DonationStatus::COMPLETE(),
            'amount' => new Money(8000, 'GBP'), // £80.00
            'feeAmountRecovered' => new Money(1600, 'GBP'), // £16.00
            'exchangeRate' => '0.8',
        ]);

        // Donation in USD: ($100 - $10 fee) / 1 = $90 USD
        Donation::factory()->create([
            'campaignId' => $campaign->id,
            'formId' => $form->id,
            'status' => DonationStatus::COMPLETE(),
            'amount' => new Money(10000, 'USD'), // $100.00
            'feeAmountRecovered' => new Money(1000, 'USD'), // $10.00
            'exchangeRate' => '1',
        ]);

        $campaignsDataQuery = CampaignsDataQuery::donations([$campaign->id]);

        // Total should be: $80.00 + $80.00 + $90.00 = $250.00
        $this->assertEquals([
            [
                'sum' => 250.00,
                'campaign_id' => $campaign->id,
            ]
        ], $campaignsDataQuery->collectIntendedAmounts());
    }
}
