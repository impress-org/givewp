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
 * @unreleased
 */
class CampaignSubscriptionQueryTest extends TestCase
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
     * @unreleased
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
     * @unreleased
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
