<?php

namespace Unit\LegacySubscriptions\Actions;

use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Donations\ValueObjects\DonationType;
use Give\Donors\Models\Donor;
use Give\Framework\Support\ValueObjects\Money;
use Give\LegacySubscriptions\Actions\EnsureSubscriptionRenewalHasCampaignId;
use Give\PaymentGateways\Gateways\TestGateway\TestGateway;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give_Subscription;
use Give_Payment;
use Give\Campaigns\Models\Campaign;
use Give\Campaigns\Repositories\CampaignRepository;

/**
 * @unreleased
 */
class EnsureSubscriptionRenewalHasCampaignIdTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @unreleased
     */
    public function testShouldAddCampaignIdToRenewalPaymentFromParentPayment()
    {
        // Create a donor
        $donor = Donor::factory()->create();

        // Create a parent payment with a campaignId using the Donation model
        $parentDonation = Donation::create([
            'status' => DonationStatus::COMPLETE(),
            'type' => DonationType::SINGLE(),
            'gatewayId' => TestGateway::id(),
            'amount' => new Money(10000, 'USD'),
            'donorId' => $donor->id,
            'firstName' => $donor->firstName,
            'lastName' => $donor->lastName,
            'email' => $donor->email,
            'formId' => 1,
            'campaignId' => 123,
        ]);

        // Create a subscription using the legacy create method
        $subscription = new Give_Subscription();
        $subscriptionId = $subscription->create([
            'customer_id' => $donor->id,
            'parent_payment_id' => $parentDonation->id,
            'form_id' => $parentDonation->formId,
            'period' => 'month',
            'frequency' => 1,
            'initial_amount' => 100,
            'recurring_amount' => 100,
            'status' => 'active',
        ]);
        $subscription = new Give_Subscription($subscriptionId);

        // Create a renewal payment without campaignId using the Donation model
        $renewalDonation = Donation::create([
            'status' => DonationStatus::RENEWAL(),
            'type' => DonationType::SINGLE(),
            'gatewayId' => TestGateway::id(),
            'amount' => new Money(10000, 'USD'),
            'donorId' => $donor->id,
            'firstName' => $donor->firstName,
            'lastName' => $donor->lastName,
            'email' => $donor->email,
            'formId' => 1,
        ]);

        // Verify renewal payment doesn't have campaignId initially
        $this->assertEmpty($renewalDonation->campaignId);

        // Create Give_Payment object for the action (since the action expects this type)
        $renewalPayment = new Give_Payment($renewalDonation->id);

        // Call the action
        $action = new EnsureSubscriptionRenewalHasCampaignId();
        $action($renewalPayment, $subscription);

        // Verify the campaignId was copied to the renewal payment
        $renewalDonationAfterUpdate = Donation::find($renewalDonation->id);
        $this->assertEquals(123, $renewalDonationAfterUpdate->campaignId);
    }

    /**
     * @unreleased
     */
    public function testShouldNotOverrideCampaignIdIfRenewalPaymentAlreadyHasOne()
    {
        // Create a donor
        $donor = Donor::factory()->create();

        // Create a parent payment with a campaignId using the Donation model
        $parentDonation = Donation::create([
            'status' => DonationStatus::COMPLETE(),
            'type' => DonationType::SINGLE(),
            'gatewayId' => TestGateway::id(),
            'amount' => new Money(10000, 'USD'),
            'donorId' => $donor->id,
            'firstName' => $donor->firstName,
            'lastName' => $donor->lastName,
            'email' => $donor->email,
            'formId' => 1,
            'campaignId' => 123,
        ]);

        // Create a subscription using the legacy create method
        $subscription = new Give_Subscription();
        $subscriptionId = $subscription->create([
            'customer_id' => $donor->id,
            'parent_payment_id' => $parentDonation->id,
            'form_id' => $parentDonation->formId,
            'period' => 'month',
            'frequency' => 1,
            'initial_amount' => 100,
            'recurring_amount' => 100,
            'status' => 'active',
        ]);
        $subscription = new Give_Subscription($subscriptionId);

        // Create a renewal payment that already has a campaignId using the Donation model
        $renewalDonation = Donation::create([
            'status' => DonationStatus::RENEWAL(),
            'type' => DonationType::SINGLE(), // Use SINGLE to avoid subscriptionId requirement in validation
            'gatewayId' => TestGateway::id(),
            'amount' => new Money(10000, 'USD'),
            'donorId' => $donor->id,
            'firstName' => $donor->firstName,
            'lastName' => $donor->lastName,
            'email' => $donor->email,
            'formId' => 1,
            'campaignId' => 456,
        ]);

        // Create Give_Payment object for the action (since the action expects this type)
        $renewalPayment = new Give_Payment($renewalDonation->id);

        // Call the action
        $action = new EnsureSubscriptionRenewalHasCampaignId();
        $action($renewalPayment, $subscription);

        // Verify the existing campaignId was not changed
        $renewalDonationAfterUpdate = Donation::find($renewalDonation->id);
        $this->assertEquals(456, $renewalDonationAfterUpdate->campaignId);
    }

    /**
     * @unreleased
     */
    public function testShouldDoNothingWhenParentPaymentHasNoCampaignId()
    {
        // Create a donor
        $donor = Donor::factory()->create();

        // Create a parent payment without a campaignId using the Donation model
        $parentDonation = Donation::create([
            'status' => DonationStatus::COMPLETE(),
            'type' => DonationType::SINGLE(),
            'gatewayId' => TestGateway::id(),
            'amount' => new Money(10000, 'USD'),
            'donorId' => $donor->id,
            'firstName' => $donor->firstName,
            'lastName' => $donor->lastName,
            'email' => $donor->email,
            'formId' => 1,
        ]);

        // Create a subscription using the legacy create method
        $subscription = new Give_Subscription();
        $subscriptionId = $subscription->create([
            'customer_id' => $donor->id,
            'parent_payment_id' => $parentDonation->id,
            'form_id' => $parentDonation->formId,
            'period' => 'month',
            'frequency' => 1,
            'initial_amount' => 100,
            'recurring_amount' => 100,
            'status' => 'active',
        ]);
        $subscription = new Give_Subscription($subscriptionId);

        // Create a renewal payment without campaignId using the Donation model
        $renewalDonation = Donation::create([
            'status' => DonationStatus::RENEWAL(),
            'type' => DonationType::SINGLE(), // Use SINGLE to avoid subscriptionId requirement in validation
            'gatewayId' => TestGateway::id(),
            'amount' => new Money(10000, 'USD'),
            'donorId' => $donor->id,
            'firstName' => $donor->firstName,
            'lastName' => $donor->lastName,
            'email' => $donor->email,
            'formId' => 1,
        ]);

        // Create Give_Payment object for the action (since the action expects this type)
        $renewalPayment = new Give_Payment($renewalDonation->id);

        // Call the action
        $action = new EnsureSubscriptionRenewalHasCampaignId();
        $action($renewalPayment, $subscription);

        // Verify no campaignId was added to the renewal payment
        $renewalDonationAfterUpdate = Donation::find($renewalDonation->id);
        $this->assertEmpty($renewalDonationAfterUpdate->campaignId);
    }

    /**
     * @unreleased
     */
    public function testShouldFindCampaignIdFromFormWhenParentPaymentHasNoCampaignId()
    {
        // Create a donor
        $donor = Donor::factory()->create();

        // Create a campaign
        $campaign = Campaign::factory()->create();

        // Create a form associated with the campaign
        $formId = $this->factory->post->create([
            'post_type' => 'give_forms',
            'post_status' => 'publish',
        ]);

        // Associate the form with the campaign using the repository method
        give(CampaignRepository::class)->addCampaignForm($campaign, $formId);

        // Create a parent payment without a campaignId using the Donation model
        $parentDonation = Donation::create([
            'status' => DonationStatus::COMPLETE(),
            'type' => DonationType::SINGLE(),
            'gatewayId' => TestGateway::id(),
            'amount' => new Money(10000, 'USD'),
            'donorId' => $donor->id,
            'firstName' => $donor->firstName,
            'lastName' => $donor->lastName,
            'email' => $donor->email,
            'formId' => $formId,
        ]);

        // Create a subscription using the legacy create method
        $subscription = new Give_Subscription();
        $subscriptionId = $subscription->create([
            'customer_id' => $donor->id,
            'parent_payment_id' => $parentDonation->id,
            'form_id' => $parentDonation->formId,
            'period' => 'month',
            'frequency' => 1,
            'initial_amount' => 100,
            'recurring_amount' => 100,
            'status' => 'active',
        ]);
        $subscription = new Give_Subscription($subscriptionId);

        // Create a renewal payment without campaignId using the Donation model
        $renewalDonation = Donation::create([
            'status' => DonationStatus::RENEWAL(),
            'type' => DonationType::SINGLE(), // Use SINGLE to avoid subscriptionId requirement in validation
            'gatewayId' => TestGateway::id(),
            'amount' => new Money(10000, 'USD'),
            'donorId' => $donor->id,
            'firstName' => $donor->firstName,
            'lastName' => $donor->lastName,
            'email' => $donor->email,
            'formId' => $formId,
        ]);

        // Verify renewal payment doesn't have campaignId initially
        $this->assertEmpty($renewalDonation->campaignId);

        // Create Give_Payment object for the action (since the action expects this type)
        $renewalPayment = new Give_Payment($renewalDonation->id);

        // Call the action
        $action = new EnsureSubscriptionRenewalHasCampaignId();
        $action($renewalPayment, $subscription);

        // Verify the campaignId was found from the form and assigned to the renewal payment
        $renewalDonationAfterUpdate = Donation::find($renewalDonation->id);
        $this->assertEquals($campaign->id, $renewalDonationAfterUpdate->campaignId);
    }
}
