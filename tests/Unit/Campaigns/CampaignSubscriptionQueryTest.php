<?php

namespace Unit\Campaigns;

use Give\Campaigns\CampaignSubscriptionQuery;
use Give\Campaigns\Models\Campaign;
use Give\DonationForms\Models\DonationForm;
use Give\Framework\Support\ValueObjects\Money;
use Give\Subscriptions\Models\Subscription;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @since 4.0.0
 */
class CampaignSubscriptionQueryTest extends TestCase
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

        for ($count = 0; $count < 2; $count++) {
            Subscription::factory()->createWithDonation(
                ['amount' => new Money(1000, 'USD')],
                ['formId' => $form->id]
            );
        }

        $query = new CampaignSubscriptionQuery($campaign);

        $this->assertEquals(2, $query->countDonations());
    }

    /**
     * @since 4.0.0
     */
    public function testSumCampaignDonations()
    {
        /** @var Campaign $campaign */
        $campaign = Campaign::factory()->create();
        $form = DonationForm::find($campaign->defaultFormId);

        for ($count = 0; $count < 2; $count++) {
            Subscription::factory()
                ->createWithDonation(
                    ['amount' => new Money(1000, 'USD')],
                    ['formId' => $form->id]
                )->createRenewal();
        }

        $query = new CampaignSubscriptionQuery($campaign);

        $this->assertEquals(20.00, $query->sumInitialAmount());
    }

    /**
     * @since 4.0.0
     */
    public function testCountCampaignDonors()
    {
        /** @var Campaign $campaign */
        $campaign = Campaign::factory()->create();
        $form = DonationForm::find($campaign->defaultFormId);

        for ($count = 0; $count < 2; $count++) {
            Subscription::factory()
                ->createWithDonation(
                    ['amount' => new Money(1000, 'USD')],
                    ['formId' => $form->id]
                )->createRenewal();
        }

        $query = new CampaignSubscriptionQuery($campaign);

        $this->assertEquals(2, $query->countDonors());
    }
}
