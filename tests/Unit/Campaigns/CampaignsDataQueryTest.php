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
 * @unreleased
 */
final class CampaignsDataQueryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @unreleased
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
}
