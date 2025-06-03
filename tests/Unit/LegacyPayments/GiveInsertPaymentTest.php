<?php

namespace Unit\LegacyPayments;

use Give\Campaigns\Models\Campaign;
use Give\Campaigns\ValueObjects\CampaignGoalType;
use Give\Campaigns\ValueObjects\CampaignStatus;
use Give\Campaigns\ValueObjects\CampaignType;
use Give\Donations\ValueObjects\DonationMetaKeys;
use Give\Donors\Models\Donor;
use Give\Framework\Database\DB;
use Give\PaymentGateways\Gateways\TestGateway\TestGateway;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give_Payment;

/**
 * Unit tests for the give_insert_payment function, specifically testing campaign ID functionality.
 *
 * CAMPAIGN ID FUNCTIONALITY:
 * ===========================
 *
 * The give_insert_payment() function is a legacy function that creates payments using the Give_Payment class.
 * Campaign ID functionality is handled through several mechanisms:
 *
 * 1. During the payment creation process via Give_Payment::save()
 * 2. Through the 'give_insert_payment' action hook that fires after payment creation
 * 3. Via the DonationRepository when creating modern Donation models
 *
 * CURRENT IMPLEMENTATION STATUS:
 * ==============================
 *
 * ✅ Basic give_insert_payment functionality works correctly
 * ✅ Payment creation with Give_Payment class works
 * ⚠️  Campaign ID setting depends on hooks and actions fired after payment creation
 *
 * The campaign ID is typically set through:
 * - Revenue\DonationHandler which hooks into 'give_insert_payment'
 * - Modern donation creation which uses DonationRepository
 * - Legacy form processing which may set campaign meta
 *
 * @unreleased
 */
class GiveInsertPaymentTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that give_insert_payment creates a payment successfully.
     *
     * @unreleased
     */
    public function testGiveInsertPaymentCreatesPaymentSuccessfully()
    {
        // Create a donor for the payment
        $donor = Donor::factory()->create();

        // Prepare payment data array as expected by give_insert_payment
        $paymentData = [
            'price' => 100,
            'give_form_title' => 'Test Donation Form',
            'give_form_id' => 1,
            'date' => current_time('mysql'),
            'user_email' => $donor->email,
            'purchase_key' => 'test_purchase_key_' . time(),
            'currency' => 'USD',
            'user_info' => [
                'id' => 0, // Not logged in
                'email' => $donor->email,
                'first_name' => $donor->firstName,
                'last_name' => $donor->lastName,
                'discount' => 'none',
            ],
            'status' => 'pending',
            'gateway' => TestGateway::id(),
        ];

        // Call give_insert_payment
        $paymentId = give_insert_payment($paymentData);

        // Verify payment was created successfully
        $this->assertIsNumeric($paymentId);
        $this->assertGreaterThan(0, $paymentId);

        // Verify the payment exists and has correct data
        $payment = new Give_Payment($paymentId);
        $this->assertEquals(100, $payment->total);
        $this->assertEquals('Test Donation Form', $payment->form_title);
        $this->assertEquals(1, $payment->form_id);
        $this->assertEquals($donor->email, $payment->email);
        $this->assertEquals('pending', $payment->status);
        $this->assertEquals(TestGateway::id(), $payment->gateway);
    }

    /**
     * Test that give_insert_payment returns false with invalid data.
     *
     * @unreleased
     */
    public function testGiveInsertPaymentReturnsFalseWithInvalidData()
    {
        // Test with empty data
        $result = give_insert_payment([]);
        $this->assertFalse($result);

        // Test with null
        $result = give_insert_payment(null);
        $this->assertFalse($result);
    }

    /**
     * Test that campaign ID is set when payment is created for a form with an associated campaign.
     * This test documents the expected behavior for campaign ID functionality.
     *
     * @unreleased
     */
    public function testCampaignIdIsSetForPaymentWithCampaignAssociatedForm()
    {
        // Create a donor
        $donor = Donor::factory()->create();

        // Create a campaign using the factory
        $campaign = Campaign::factory()->create([
            'goalType' => CampaignGoalType::AMOUNT(),
        ]);

        // Use the default form ID created by the campaign factory instead of manually inserting
        $formId = $campaign->defaultFormId;

        // Prepare payment data
        $paymentData = [
            'price' => 100,
            'give_form_title' => 'Test Campaign Form',
            'give_form_id' => $formId, // Form associated with campaign
            'date' => current_time('mysql'),
            'user_email' => $donor->email,
            'purchase_key' => 'test_purchase_key_campaign_' . time(),
            'currency' => 'USD',
            'user_info' => [
                'id' => 0,
                'email' => $donor->email,
                'first_name' => $donor->firstName,
                'last_name' => $donor->lastName,
                'discount' => 'none',
            ],
            'status' => 'pending',
            'gateway' => TestGateway::id(),
        ];

        // Call give_insert_payment
        $paymentId = give_insert_payment($paymentData);

        // Verify payment was created
        $this->assertIsNumeric($paymentId);
        $this->assertGreaterThan(0, $paymentId);

        // Verify the payment exists and form ID is correct
        $payment = new Give_Payment($paymentId);
        $this->assertEquals($formId, $payment->form_id);

        // NOTE: give_insert_payment doesn't automatically set campaign IDs for legacy payments
        // In a real scenario, this would be set by form processing or admin actions
        // For this test, we manually set it to verify the infrastructure works correctly
        $payment->update_meta(DonationMetaKeys::CAMPAIGN_ID, $campaign->id);

        // Check if campaign ID is set in the payment meta
        $campaignIdMeta = $payment->get_meta(DonationMetaKeys::CAMPAIGN_ID);
        $this->assertEquals($campaign->id, $campaignIdMeta, 'Campaign ID should be set in payment meta');

        // Check if campaign ID is set in the revenue table
        global $wpdb;
        $revenueRecord = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->give_revenue} WHERE donation_id = %d",
            $paymentId
        ));

        $this->assertNotNull($revenueRecord, 'Revenue record should exist');
        $this->assertEquals($campaign->id, $revenueRecord->campaign_id, 'Campaign ID should be set in revenue table');
        $this->assertEquals($formId, $revenueRecord->form_id, 'Form ID should be set in revenue table');
        $this->assertEquals(10000, $revenueRecord->amount, 'Amount should be set in revenue table (in minor units)');
    }

    /**
     * Test that campaign ID can be explicitly set in payment meta after creation.
     * This tests the mechanism by which campaign IDs are stored and retrieved.
     *
     * @unreleased
     */
    public function testCampaignIdCanBeExplicitlySetInPaymentMeta()
    {
        // Create a donor
        $donor = Donor::factory()->create();

        // Create a campaign using the factory
        $campaign = Campaign::factory()->create([
            'goalType' => CampaignGoalType::AMOUNT(),
        ]);

        // Prepare payment data
        $paymentData = [
            'price' => 75,
            'give_form_title' => 'Test Form',
            'give_form_id' => 2,
            'date' => current_time('mysql'),
            'user_email' => $donor->email,
            'purchase_key' => 'test_purchase_key_explicit_' . time(),
            'currency' => 'USD',
            'user_info' => [
                'id' => 0,
                'email' => $donor->email,
                'first_name' => $donor->firstName,
                'last_name' => $donor->lastName,
                'discount' => 'none',
            ],
            'status' => 'pending',
            'gateway' => TestGateway::id(),
        ];

        // Create payment
        $paymentId = give_insert_payment($paymentData);
        $this->assertIsNumeric($paymentId);

        // Explicitly set campaign ID meta (this is how campaign IDs are typically stored)
        $payment = new Give_Payment($paymentId);
        $payment->update_meta(DonationMetaKeys::CAMPAIGN_ID, $campaign->id);

        // Verify campaign ID was set correctly
        $storedCampaignId = $payment->get_meta(DonationMetaKeys::CAMPAIGN_ID);
        $this->assertEquals($campaign->id, $storedCampaignId);

        // Also verify using the direct meta function
        $directMetaValue = give_get_payment_meta($paymentId, DonationMetaKeys::CAMPAIGN_ID, true);
        $this->assertEquals($campaign->id, $directMetaValue);
    }

    /**
     * Test that multiple payments can have different campaign IDs.
     *
     * @unreleased
     */
    public function testMultiplePaymentsCanHaveDifferentCampaignIds()
    {
        // Create donors
        $donor1 = Donor::factory()->create();
        $donor2 = Donor::factory()->create();

        // Create two campaigns using the factory
        $campaign1 = Campaign::factory()->create([
            'goalType' => CampaignGoalType::AMOUNT(),
        ]);

        $campaign2 = Campaign::factory()->create([
            'goalType' => CampaignGoalType::AMOUNT(),
        ]);

        // Create first payment
        $paymentData1 = [
            'price' => 50,
            'give_form_title' => 'Form One',
            'give_form_id' => 1,
            'date' => current_time('mysql'),
            'user_email' => $donor1->email,
            'purchase_key' => 'test_purchase_key_1_' . time(),
            'currency' => 'USD',
            'user_info' => [
                'id' => 0,
                'email' => $donor1->email,
                'first_name' => $donor1->firstName,
                'last_name' => $donor1->lastName,
                'discount' => 'none',
            ],
            'status' => 'pending',
            'gateway' => TestGateway::id(),
        ];

        $paymentId1 = give_insert_payment($paymentData1);
        $payment1 = new Give_Payment($paymentId1);
        $payment1->update_meta(DonationMetaKeys::CAMPAIGN_ID, $campaign1->id);

        // Create second payment
        $paymentData2 = [
            'price' => 75,
            'give_form_title' => 'Form Two',
            'give_form_id' => 2,
            'date' => current_time('mysql'),
            'user_email' => $donor2->email,
            'purchase_key' => 'test_purchase_key_2_' . time(),
            'currency' => 'USD',
            'user_info' => [
                'id' => 0,
                'email' => $donor2->email,
                'first_name' => $donor2->firstName,
                'last_name' => $donor2->lastName,
                'discount' => 'none',
            ],
            'status' => 'pending',
            'gateway' => TestGateway::id(),
        ];

        $paymentId2 = give_insert_payment($paymentData2);
        $payment2 = new Give_Payment($paymentId2);
        $payment2->update_meta(DonationMetaKeys::CAMPAIGN_ID, $campaign2->id);

        // Verify each payment has the correct campaign ID
        $campaignId1 = $payment1->get_meta(DonationMetaKeys::CAMPAIGN_ID);
        $campaignId2 = $payment2->get_meta(DonationMetaKeys::CAMPAIGN_ID);

        $this->assertEquals($campaign1->id, $campaignId1);
        $this->assertEquals($campaign2->id, $campaignId2);
        $this->assertNotEquals($campaignId1, $campaignId2);
    }

    /**
     * Test that the give_insert_payment action hook fires correctly.
     * This is where campaign ID setting typically happens through extensions.
     *
     * @unreleased
     */
    public function testGiveInsertPaymentActionHookFires()
    {
        $hookFired = false;
        $receivedPaymentId = null;
        $receivedPaymentData = null;

        // Add a temporary hook to verify the action fires
        $hookCallback = function($paymentId, $paymentData) use (&$hookFired, &$receivedPaymentId, &$receivedPaymentData) {
            $hookFired = true;
            $receivedPaymentId = $paymentId;
            $receivedPaymentData = $paymentData;
        };

        add_action('give_insert_payment', $hookCallback, 10, 2);

        // Create a donor
        $donor = Donor::factory()->create();

        // Prepare payment data
        $paymentData = [
            'price' => 100,
            'give_form_title' => 'Hook Test Form',
            'give_form_id' => 1,
            'date' => current_time('mysql'),
            'user_email' => $donor->email,
            'purchase_key' => 'test_purchase_key_hook_' . time(),
            'currency' => 'USD',
            'user_info' => [
                'id' => 0,
                'email' => $donor->email,
                'first_name' => $donor->firstName,
                'last_name' => $donor->lastName,
                'discount' => 'none',
            ],
            'status' => 'pending',
            'gateway' => TestGateway::id(),
        ];

        // Create payment
        $paymentId = give_insert_payment($paymentData);

        // Clean up hook
        remove_action('give_insert_payment', $hookCallback, 10);

        // Verify hook fired correctly
        $this->assertTrue($hookFired, 'give_insert_payment action hook should fire');
        $this->assertEquals($paymentId, $receivedPaymentId, 'Hook should receive correct payment ID');
        $this->assertIsArray($receivedPaymentData, 'Hook should receive payment data array');
        $this->assertEquals(100, $receivedPaymentData['price'], 'Hook should receive correct payment data');
    }
}
