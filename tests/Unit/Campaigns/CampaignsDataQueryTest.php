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

        $donation1 = Donation::factory()->create([
            'campaignId' => $campaign->id,
            'formId' => $form->id,
            'status' => DonationStatus::COMPLETE(),
            'amount' => new Money(1051, 'USD'),
            'feeAmountRecovered' => new Money(35, 'USD'),
        ]);

        give_update_meta($donation1->id, '_give_fee_donation_amount', 10.16);

        $donation2 = Donation::factory()->create([
            'campaignId' => $campaign->id,
            'formId' => $form->id,
            'status' => DonationStatus::COMPLETE(),
            'amount' => new Money(1051, 'USD'),
            'feeAmountRecovered' => new Money(35, 'USD'),
        ]);

        give_update_meta($donation2->id, '_give_fee_donation_amount', 10.16);

        $campaignsDataQuery = CampaignsDataQuery::donations([$campaign->id]);

        $this->assertEquals([
            [
                'sum' => 20.32,
                'campaign_id' => $campaign->id,
            ]
        ], $campaignsDataQuery->collectIntendedAmounts());
    }
}
