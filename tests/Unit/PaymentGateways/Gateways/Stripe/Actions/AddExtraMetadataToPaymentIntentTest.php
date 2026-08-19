<?php

namespace Give\Tests\Unit\PaymentGateways\Gateways\Stripe\Actions;

use Give\PaymentGateways\Gateways\Stripe\Actions\AddExtraMetadataToPaymentIntent;
use Give\Campaigns\Models\Campaign;
use Give\Campaigns\Repositories\CampaignRepository;
use Give\Donations\Models\Donation;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @since TBD
 */
class AddExtraMetadataToPaymentIntentTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @since TBD
     */
    public function testShouldAddCampaignNameToMetadata()
    {
        $campaign = Campaign::factory()->create(['title' => 'Save The Whales']);
        $donation = Donation::factory()->create(['formId' => $campaign->defaultFormId]);

        $metadata = (new AddExtraMetadataToPaymentIntent())([], $donation->id);

        $this->assertSame('Save The Whales', $metadata['Campaign Name']);
    }

    /**
     * @since TBD
     */
    public function testShouldTruncateCampaignNameExceedingStripeMaxLength()
    {
        $campaign = Campaign::factory()->create(['title' => str_repeat('a', 600)]);
        $donation = Donation::factory()->create(['formId' => $campaign->defaultFormId]);

        $metadata = (new AddExtraMetadataToPaymentIntent())([], $donation->id);

        $this->assertSame(str_repeat('a', 497) . '...', $metadata['Campaign Name']);
        $this->assertSame(
            AddExtraMetadataToPaymentIntent::MAX_LENGTH,
            mb_strlen($metadata['Campaign Name'])
        );
    }

    /**
     * @since TBD
     */
    public function testShouldNotAddCampaignNameWhenCampaignTitleIsEmpty()
    {
        $campaign = Campaign::factory()->create();
        $donation = Donation::factory()->create(['formId' => $campaign->defaultFormId]);

        $campaign->title = '';
        give(CampaignRepository::class)->update($campaign);

        $metadata = (new AddExtraMetadataToPaymentIntent())([], $donation->id);

        $this->assertArrayNotHasKey('Campaign Name', $metadata);
    }

    /**
     * @since TBD
     */
    public function testShouldReturnMetadataUnchangedWhenDonationDoesNotExist()
    {
        $metadata = ['Email' => 'donor@example.com'];

        $this->assertSame(
            $metadata,
            (new AddExtraMetadataToPaymentIntent())($metadata, 999999)
        );
    }
}
