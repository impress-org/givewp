<?php

namespace Unit\LegacySubscriptions;

use Give\Donations\ValueObjects\DonationMetaKeys;
use Give\Donors\Models\Donor;
use Give\PaymentGateways\Gateways\TestGateway\TestGateway;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give_Subscription;
use Give_Payment;

/**
 * Unit tests for Give_Subscription class, specifically testing the add_payment method.
 *
 * CAMPAIGN ID BACKWARDS COMPATIBILITY:
 * ===================================
 *
 * The Give_Subscription::add_payment() method creates renewal payments for subscriptions.
 * Campaign ID functionality is now handled through backwards compatibility built directly
 * into the Give_Payment class and give_insert_payment function, eliminating the need
 * for separate action hooks.
 *
 * IMPLEMENTATION STATUS:
 * =====================
 *
 * ✅ Basic add_payment functionality works correctly
 * ✅ Duplicate transaction ID prevention works
 * ✅ Campaign ID backwards compatibility implemented and working
 * ✅ Automatic campaign ID derivation from form_id
 * ✅ Direct campaign_id property access on Give_Payment objects
 *
 * BACKWARDS COMPATIBILITY FEATURES:
 * ================================
 *
 * 1. Campaign ID Property Access:
 *    - Give_Payment objects now have direct campaign_id property access
 *    - Properties can be read/written using $payment->campaign_id
 *    - Values are automatically saved to payment meta (_give_campaign_id)
 *
 * 2. Automatic Campaign ID Derivation:
 *    - When creating payments via give_insert_payment(), campaign_id is automatically
 *      derived from the form_id if a campaign exists for that form
 *    - This ensures compatibility with existing code that expects campaign_id
 *
 * 3. Subscription Renewal Compatibility:
 *    - Renewal payments created through add_payment() inherit campaign functionality
 *    - No special action hooks or middleware required
 *
 * TESTING APPROACH:
 * ================
 *
 * Tests cover both the core add_payment functionality and the new backwards
 * compatibility features to ensure proper campaign ID handling across all scenarios.
 *
 * @since 4.3.2
 */
class GiveSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * ========================================
     * BACKWARDS COMPATIBILITY TESTS
     * ========================================
     *
     * The following tests verify the new backwards compatibility features
     * for campaign ID functionality built into Give_Payment objects.
     */

    /**
     * Test that campaign_id is automatically derived from form_id when creating payments.
     * This tests the new automatic derivation functionality in give_insert_payment.
     *
     * @since 4.3.2
     */
    public function testCampaignIdAutomaticallyDerivedFromFormId()
    {
        // Create a campaign
        $campaign = \Give\Campaigns\Models\Campaign::factory()->create([
            'goalType' => \Give\Campaigns\ValueObjects\CampaignGoalType::AMOUNT(),
        ]);

        // Get the form associated with the campaign
        $formId = $campaign->defaultFormId;

        // Create a donor
        $donor = Donor::factory()->create();

        // Create payment data for give_insert_payment
        $paymentData = [
            'price' => 100,
            'give_form_title' => 'Test Campaign Form',
            'give_form_id' => $formId,
            'date' => current_time('mysql'),
            'user_email' => $donor->email,
            'purchase_key' => 'test_auto_campaign_' . time(),
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

        // Create payment using give_insert_payment (this should auto-derive campaign_id)
        $paymentId = give_insert_payment($paymentData);

        // Verify payment was created
        $this->assertIsNumeric($paymentId);
        $this->assertGreaterThan(0, $paymentId);

        // Create payment object and verify campaign_id was automatically derived
        $payment = new Give_Payment($paymentId);
        $this->assertEquals($campaign->id, $payment->campaign_id, 'Campaign ID should be automatically derived from form ID');
    }

    /**
     * Test that campaign_id property exists and works correctly on legacy Give_Payment objects.
     * This verifies our new backwards compatibility for campaign_id property access.
     *
     * @since 4.3.2
     */
    public function testCampaignIdPropertyAccessOnLegacyPayment()
    {
        // Create a donor
        $donor = Donor::factory()->create();

        // Create a payment using legacy Give_Payment class
        $payment = new Give_Payment();
        $payment->total = 100;
        $payment->form_id = 1;
        $payment->customer_id = $donor->id;
        $payment->first_name = $donor->firstName;
        $payment->last_name = $donor->lastName;
        $payment->email = $donor->email;
        $payment->currency = 'USD';
        $payment->status = 'publish';
        $payment->gateway = TestGateway::id();
        $payment->mode = give_is_test_mode() ? 'test' : 'live';
        $payment->transaction_id = 'test_campaign_property';
        $payment->save();

        // Verify campaign_id defaults to 0
        $this->assertEquals(0, $payment->campaign_id, 'Campaign ID should default to 0');

        // Set campaign_id using the property
        $payment->campaign_id = 456;
        $payment->save();

        // Verify the campaign_id was saved correctly
        $this->assertEquals(456, $payment->campaign_id, 'Campaign ID should be set to 456');

        // Create a new payment object to verify persistence
        $reloadedPayment = new Give_Payment($payment->ID);
        $this->assertEquals(456, $reloadedPayment->campaign_id, 'Campaign ID should persist when reloaded');

        // Verify the meta was saved correctly
        $savedCampaignId = give_get_meta($payment->ID, '_give_campaign_id', true);
        $this->assertEquals(456, $savedCampaignId, 'Campaign ID should be saved in payment meta');
    }

    /**
     * Test that add_payment method successfully creates a renewal payment.
     *
     * @since 4.3.2
     */
    public function testAddPaymentCreatesRenewalPayment()
    {
        // Create a donor
        $donor = Donor::factory()->create();

        // Create a parent payment manually using Give_Payment
        $parentPayment = new Give_Payment();
        $parentPayment->total = 100;
        $parentPayment->form_id = 1;
        $parentPayment->customer_id = $donor->id;
        $parentPayment->first_name = $donor->firstName;
        $parentPayment->last_name = $donor->lastName;
        $parentPayment->email = $donor->email;
        $parentPayment->currency = 'USD';
        $parentPayment->status = 'publish';
        $parentPayment->gateway = TestGateway::id();
        $parentPayment->mode = give_is_test_mode() ? 'test' : 'live';
        $parentPayment->transaction_id = 'parent_txn_123';
        $parentPayment->save();

        // Create a subscription
        $subscription = new Give_Subscription();
        $subscriptionId = $subscription->create([
            'customer_id' => $donor->id,
            'parent_payment_id' => $parentPayment->ID,
            'form_id' => 1,
            'period' => 'month',
            'frequency' => 1,
            'initial_amount' => 100,
            'recurring_amount' => 80,
            'status' => 'active',
        ]);
        $subscription = new Give_Subscription($subscriptionId);

        // Call add_payment to create a renewal payment
        $renewalArgs = [
            'amount' => 80,
            'transaction_id' => 'test_renewal_txn_123',
            'gateway' => TestGateway::id(),
        ];
        $result = $subscription->add_payment($renewalArgs);

        // Verify the payment was added successfully
        $this->assertTrue($result);

        // Get the child payments (renewals)
        $childPayments = $subscription->get_child_payments();
        $this->assertCount(1, $childPayments);

        // Get the renewal payment
        $renewalPayment = new Give_Payment($childPayments[0]->ID);

        // Assert basic properties of the renewal payment
        $this->assertEquals(80, $renewalPayment->total);
        $this->assertEquals('test_renewal_txn_123', $renewalPayment->transaction_id);
        $this->assertEquals('give_subscription', $renewalPayment->status);
        $this->assertEquals($parentPayment->ID, $renewalPayment->parent_payment);
        $this->assertEquals($subscription->id, $renewalPayment->get_meta('subscription_id'));
    }

    /**
     * Test that add_payment method returns false when payment with same transaction ID already exists.
     *
     * @since 4.3.2
     */
    public function testAddPaymentReturnsFalseWhenPaymentExists()
    {
        // Create a donor
        $donor = Donor::factory()->create();

        // Create a parent payment
        $parentPayment = new Give_Payment();
        $parentPayment->total = 100;
        $parentPayment->form_id = 1;
        $parentPayment->customer_id = $donor->id;
        $parentPayment->first_name = $donor->firstName;
        $parentPayment->last_name = $donor->lastName;
        $parentPayment->email = $donor->email;
        $parentPayment->currency = 'USD';
        $parentPayment->status = 'publish';
        $parentPayment->gateway = TestGateway::id();
        $parentPayment->mode = give_is_test_mode() ? 'test' : 'live';
        $parentPayment->transaction_id = 'parent_txn_789';
        $parentPayment->save();

        // Create a subscription
        $subscription = new Give_Subscription();
        $subscriptionId = $subscription->create([
            'customer_id' => $donor->id,
            'parent_payment_id' => $parentPayment->ID,
            'form_id' => 1,
            'period' => 'month',
            'frequency' => 1,
            'initial_amount' => 100,
            'recurring_amount' => 80,
            'status' => 'active',
        ]);
        $subscription = new Give_Subscription($subscriptionId);

        $renewalArgs = [
            'amount' => 80,
            'transaction_id' => 'duplicate_txn_123',
            'gateway' => TestGateway::id(),
        ];

        // First call should succeed
        $result1 = $subscription->add_payment($renewalArgs);
        $this->assertTrue($result1);

        // Second call with same transaction ID should fail
        $result2 = $subscription->add_payment($renewalArgs);
        $this->assertFalse($result2, 'Adding payment with duplicate transaction ID should return false');
    }

    /**
     * Test that add_payment properly handles anonymous donations by copying the _give_anonymous_donation meta.
     *
     * @since 4.3.2
     */
    public function testAddPaymentCopiesAnonymousDonationMeta()
    {
        // Create a donor
        $donor = Donor::factory()->create();

        // Create a parent payment that is anonymous
        $parentPayment = new Give_Payment();
        $parentPayment->total = 100;
        $parentPayment->form_id = 1;
        $parentPayment->customer_id = $donor->id;
        $parentPayment->first_name = $donor->firstName;
        $parentPayment->last_name = $donor->lastName;
        $parentPayment->email = $donor->email;
        $parentPayment->currency = 'USD';
        $parentPayment->status = 'publish';
        $parentPayment->gateway = TestGateway::id();
        $parentPayment->mode = give_is_test_mode() ? 'test' : 'live';
        $parentPayment->transaction_id = 'parent_anonymous';
        $parentPayment->save();

        // Mark the parent payment as anonymous
        $parentPayment->update_meta('_give_anonymous_donation', 1);

        // Create a subscription
        $subscription = new Give_Subscription();
        $subscriptionId = $subscription->create([
            'customer_id' => $donor->id,
            'parent_payment_id' => $parentPayment->ID,
            'form_id' => 1,
            'period' => 'month',
            'frequency' => 1,
            'initial_amount' => 100,
            'recurring_amount' => 80,
            'status' => 'active',
        ]);
        $subscription = new Give_Subscription($subscriptionId);

        // Call add_payment to create a renewal payment
        $renewalArgs = [
            'amount' => 80,
            'transaction_id' => 'test_renewal_anonymous',
            'gateway' => TestGateway::id(),
        ];
        $result = $subscription->add_payment($renewalArgs);

        // Verify the payment was added successfully
        $this->assertTrue($result);

        // Get the child payments (renewals)
        $childPayments = $subscription->get_child_payments();
        $this->assertCount(1, $childPayments);

        // Get the renewal payment and verify it's marked as anonymous
        $renewalPayment = new Give_Payment($childPayments[0]->ID);
        $this->assertEquals(1, $renewalPayment->get_meta('_give_anonymous_donation'));
    }

    /**
     * Test that add_payment properly handles custom post_date when provided.
     *
     * @since 4.3.2
     */
    public function testAddPaymentWithCustomDate()
    {
        // Create a donor
        $donor = Donor::factory()->create();

        // Create a parent payment
        $parentPayment = new Give_Payment();
        $parentPayment->total = 100;
        $parentPayment->form_id = 1;
        $parentPayment->customer_id = $donor->id;
        $parentPayment->first_name = $donor->firstName;
        $parentPayment->last_name = $donor->lastName;
        $parentPayment->email = $donor->email;
        $parentPayment->currency = 'USD';
        $parentPayment->status = 'publish';
        $parentPayment->gateway = TestGateway::id();
        $parentPayment->mode = give_is_test_mode() ? 'test' : 'live';
        $parentPayment->transaction_id = 'parent_custom_date';
        $parentPayment->save();

        // Create a subscription
        $subscription = new Give_Subscription();
        $subscriptionId = $subscription->create([
            'customer_id' => $donor->id,
            'parent_payment_id' => $parentPayment->ID,
            'form_id' => 1,
            'period' => 'month',
            'frequency' => 1,
            'initial_amount' => 100,
            'recurring_amount' => 80,
            'status' => 'active',
        ]);
        $subscription = new Give_Subscription($subscriptionId);

        // Call add_payment with a custom date
        $customDate = '2023-06-15 10:30:00';
        $renewalArgs = [
            'amount' => 80,
            'transaction_id' => 'test_renewal_custom_date',
            'gateway' => TestGateway::id(),
            'post_date' => $customDate,
        ];
        $result = $subscription->add_payment($renewalArgs);

        // Verify the payment was added successfully
        $this->assertTrue($result);

        // Get the child payments (renewals)
        $childPayments = $subscription->get_child_payments();
        $this->assertCount(1, $childPayments);

        // Get the renewal payment and verify the custom date was set
        $renewalPayment = $childPayments[0];
        $this->assertEquals($customDate, $renewalPayment->post_date);
    }

    /**
     * Test that add_payment correctly increases form and donor statistics.
     *
     * @since 4.3.2
     */
    public function testAddPaymentIncreasesStatistics()
    {
        // Create a donor
        $donor = Donor::factory()->create();

        // Create a parent payment
        $parentPayment = new Give_Payment();
        $parentPayment->total = 100;
        $parentPayment->form_id = 1;
        $parentPayment->customer_id = $donor->id;
        $parentPayment->first_name = $donor->firstName;
        $parentPayment->last_name = $donor->lastName;
        $parentPayment->email = $donor->email;
        $parentPayment->currency = 'USD';
        $parentPayment->status = 'publish';
        $parentPayment->gateway = TestGateway::id();
        $parentPayment->mode = give_is_test_mode() ? 'test' : 'live';
        $parentPayment->transaction_id = 'parent_stats_test';
        $parentPayment->save();

        // Create a subscription
        $subscription = new Give_Subscription();
        $subscriptionId = $subscription->create([
            'customer_id' => $donor->id,
            'parent_payment_id' => $parentPayment->ID,
            'form_id' => 1,
            'period' => 'month',
            'frequency' => 1,
            'initial_amount' => 100,
            'recurring_amount' => 80,
            'status' => 'active',
        ]);
        $subscription = new Give_Subscription($subscriptionId);

        // Get initial donor stats
        $donorModel = new \Give_Donor($donor->id);
        $initialPurchaseCount = $donorModel->purchase_count;
        $initialValue = $donorModel->purchase_value;

        // Call add_payment to create a renewal payment
        $renewalArgs = [
            'amount' => 80,
            'transaction_id' => 'test_renewal_stats',
            'gateway' => TestGateway::id(),
        ];
        $result = $subscription->add_payment($renewalArgs);

        // Verify the payment was added successfully
        $this->assertTrue($result);

        // Refresh donor data and verify stats were increased
        $donorModel = new \Give_Donor($donor->id);
        $this->assertEquals($initialPurchaseCount + 1, $donorModel->purchase_count);
        $this->assertEquals($initialValue + 80, $donorModel->purchase_value);
    }

    /**
     * Test that add_payment sanitizes amounts correctly, especially for webhook responses.
     *
     * @since 4.3.2
     */
    public function testAddPaymentSanitizesAmounts()
    {
        // Create a donor
        $donor = Donor::factory()->create();

        // Create a parent payment
        $parentPayment = new Give_Payment();
        $parentPayment->total = 100;
        $parentPayment->form_id = 1;
        $parentPayment->customer_id = $donor->id;
        $parentPayment->first_name = $donor->firstName;
        $parentPayment->last_name = $donor->lastName;
        $parentPayment->email = $donor->email;
        $parentPayment->currency = 'USD';
        $parentPayment->status = 'publish';
        $parentPayment->gateway = TestGateway::id();
        $parentPayment->mode = give_is_test_mode() ? 'test' : 'live';
        $parentPayment->transaction_id = 'parent_sanitize_test';
        $parentPayment->save();

        // Create a subscription
        $subscription = new Give_Subscription();
        $subscriptionId = $subscription->create([
            'customer_id' => $donor->id,
            'parent_payment_id' => $parentPayment->ID,
            'form_id' => 1,
            'period' => 'month',
            'frequency' => 1,
            'initial_amount' => 100,
            'recurring_amount' => 80,
            'status' => 'active',
        ]);
        $subscription = new Give_Subscription($subscriptionId);

        // Test with an amount that needs sanitization (string with decimal)
        $renewalArgs = [
            'amount' => '85.50',
            'transaction_id' => 'test_renewal_sanitize',
            'gateway' => TestGateway::id(),
        ];
        $result = $subscription->add_payment($renewalArgs);

        // Verify the payment was added successfully
        $this->assertTrue($result);

        // Get the child payments and verify the amount was properly converted to float
        $childPayments = $subscription->get_child_payments();
        $this->assertCount(1, $childPayments);

        $renewalPayment = new Give_Payment($childPayments[0]->ID);
        $this->assertEquals(85.5, $renewalPayment->total);
    }

    /**
     * Test that add_payment handles missing gateway parameter correctly.
     *
     * @since 4.3.2
     */
    public function testAddPaymentWithMissingGateway()
    {
        // Create a donor
        $donor = Donor::factory()->create();

        // Create a parent payment
        $parentPayment = new Give_Payment();
        $parentPayment->total = 100;
        $parentPayment->form_id = 1;
        $parentPayment->customer_id = $donor->id;
        $parentPayment->first_name = $donor->firstName;
        $parentPayment->last_name = $donor->lastName;
        $parentPayment->email = $donor->email;
        $parentPayment->currency = 'USD';
        $parentPayment->status = 'publish';
        $parentPayment->gateway = TestGateway::id();
        $parentPayment->mode = give_is_test_mode() ? 'test' : 'live';
        $parentPayment->transaction_id = 'parent_missing_gateway';
        $parentPayment->save();

        // Create a subscription
        $subscription = new Give_Subscription();
        $subscriptionId = $subscription->create([
            'customer_id' => $donor->id,
            'parent_payment_id' => $parentPayment->ID,
            'form_id' => 1,
            'period' => 'month',
            'frequency' => 1,
            'initial_amount' => 100,
            'recurring_amount' => 80,
            'status' => 'active',
        ]);
        $subscription = new Give_Subscription($subscriptionId);

        // Call add_payment without specifying gateway (should inherit from parent)
        $renewalArgs = [
            'amount' => 80,
            'transaction_id' => 'test_renewal_no_gateway',
            // 'gateway' => '', // Intentionally omitted
        ];
        $result = $subscription->add_payment($renewalArgs);

        // Verify the payment was added successfully
        $this->assertTrue($result);

        // Get the renewal payment and verify it inherited the gateway from parent
        $childPayments = $subscription->get_child_payments();
        $this->assertCount(1, $childPayments);

        $renewalPayment = new Give_Payment($childPayments[0]->ID);
        $this->assertEquals(TestGateway::id(), $renewalPayment->gateway);
    }

    /**
     * Test that subscription renewal payments can access campaign_id through the Give_Payment object.
     * This verifies the backwards compatibility works in the subscription renewal context.
     *
     * @since 4.3.2
     */
    public function testSubscriptionRenewalCampaignIdAccess()
    {
        // Create a donor
        $donor = Donor::factory()->create();

        // Create a parent payment manually using Give_Payment
        $parentPayment = new Give_Payment();
        $parentPayment->total = 100;
        $parentPayment->form_id = 1;
        $parentPayment->customer_id = $donor->id;
        $parentPayment->first_name = $donor->firstName;
        $parentPayment->last_name = $donor->lastName;
        $parentPayment->email = $donor->email;
        $parentPayment->currency = 'USD';
        $parentPayment->status = 'publish';
        $parentPayment->gateway = TestGateway::id();
        $parentPayment->mode = give_is_test_mode() ? 'test' : 'live';
        $parentPayment->transaction_id = 'parent_with_campaign';

        // Set campaign_id on parent payment
        $parentPayment->campaign_id = 789;
        $parentPayment->save();

        // Verify parent payment has campaign_id
        $this->assertEquals(789, $parentPayment->campaign_id, 'Parent payment should have campaign ID set');

        // Create a subscription using the legacy create method
        $subscription = new Give_Subscription();
        $subscriptionId = $subscription->create([
            'customer_id' => $donor->id,
            'parent_payment_id' => $parentPayment->ID,
            'form_id' => 1,
            'period' => 'month',
            'frequency' => 1,
            'initial_amount' => 100,
            'recurring_amount' => 80,
            'status' => 'active',
        ]);
        $subscription = new Give_Subscription($subscriptionId);

        // Call add_payment to create a renewal payment
        $renewalArgs = [
            'amount' => 80,
            'transaction_id' => 'test_renewal_campaign_access',
            'gateway' => TestGateway::id(),
        ];
        $result = $subscription->add_payment($renewalArgs);

        // Verify the payment was added successfully
        $this->assertTrue($result, 'Renewal payment should be created successfully');

        // Get the child payments (renewals)
        $childPayments = $subscription->get_child_payments();
        $this->assertCount(1, $childPayments, 'Should have one renewal payment');

        // Get the renewal payment object
        $renewalPayment = new Give_Payment($childPayments[0]->ID);

        // Verify that campaign_id property is accessible (should default to 0 since auto-copying is disabled)
        $this->assertIsNumeric($renewalPayment->campaign_id, 'Campaign ID should be accessible as a numeric value');

        // Manually set campaign_id on renewal payment to test property access
        $renewalPayment->campaign_id = 999;
        $renewalPayment->save();

        // Verify campaign_id was set correctly
        $this->assertEquals(999, $renewalPayment->campaign_id, 'Campaign ID should be settable on renewal payment');

        // Reload payment to verify persistence
        $reloadedRenewalPayment = new Give_Payment($renewalPayment->ID);
        $this->assertEquals(999, $reloadedRenewalPayment->campaign_id, 'Campaign ID should persist on renewal payment');
    }

    /**
     * Test that add_payment automatically adds _give_campaign_id meta to renewal payments
     * when a campaign exists for the form, without explicitly specifying campaign_id.
     *
     * @since 4.3.2
     */
    public function testAddPaymentAutomaticallyAddsCampaignIdMeta()
    {
        // Create a campaign
        $campaign = \Give\Campaigns\Models\Campaign::factory()->create([
            'goalType' => \Give\Campaigns\ValueObjects\CampaignGoalType::AMOUNT(),
        ]);

        // Get the form associated with the campaign
        $formId = $campaign->defaultFormId;

        // Create a donor
        $donor = Donor::factory()->create();

        // Create a parent payment with the campaign form
        $parentPayment = new Give_Payment();
        $parentPayment->total = 100;
        $parentPayment->form_id = $formId;
        $parentPayment->customer_id = $donor->id;
        $parentPayment->first_name = $donor->firstName;
        $parentPayment->last_name = $donor->lastName;
        $parentPayment->email = $donor->email;
        $parentPayment->currency = 'USD';
        $parentPayment->status = 'publish';
        $parentPayment->gateway = TestGateway::id();
        $parentPayment->mode = give_is_test_mode() ? 'test' : 'live';
        $parentPayment->transaction_id = 'parent_campaign_auto';
        $parentPayment->save();

        // Create a subscription
        $subscription = new Give_Subscription();
        $subscriptionId = $subscription->create([
            'customer_id' => $donor->id,
            'parent_payment_id' => $parentPayment->ID,
            'form_id' => $formId,
            'period' => 'month',
            'frequency' => 1,
            'initial_amount' => 100,
            'recurring_amount' => 80,
            'status' => 'active',
        ]);
        $subscription = new Give_Subscription($subscriptionId);

        // Call add_payment to create a renewal payment WITHOUT specifying campaign_id
        $renewalArgs = [
            'amount' => 80,
            'transaction_id' => 'test_renewal_auto_campaign',
            'gateway' => TestGateway::id(),
        ];
        $result = $subscription->add_payment($renewalArgs);

        // Verify the payment was added successfully
        $this->assertTrue($result, 'Renewal payment should be created successfully');

        // Get the child payments (renewals)
        $childPayments = $subscription->get_child_payments();
        $this->assertCount(1, $childPayments, 'Should have one renewal payment');

        // Get the renewal payment object
        $renewalPayment = new Give_Payment($childPayments[0]->ID);

        // Verify that campaign_id is automatically derived and accessible
        $this->assertEquals($campaign->id, $renewalPayment->campaign_id, 'Campaign ID should be automatically derived from form ID');

        // Verify the _give_campaign_id meta was saved to the database
        $savedCampaignId = give_get_meta($renewalPayment->ID, '_give_campaign_id', true);
        $this->assertEquals($campaign->id, $savedCampaignId, 'Campaign ID should be saved in payment meta as _give_campaign_id');

        // Verify the campaign meta key exists in the database
        $metaExists = give_get_meta($renewalPayment->ID, '_give_campaign_id', true);
        $this->assertNotEmpty($metaExists, '_give_campaign_id meta should exist and not be empty');
        $this->assertIsNumeric($metaExists, '_give_campaign_id meta should be numeric');
    }

    /**
     * Test that the give_derive_campaign_id_from_form_id() utility function works correctly.
     *
     * @since 4.3.2
     */
    public function testGiveDeriveCampaignIdFromFormIdFunction()
    {
        // Test with a form that has no campaign - should return 0
        $result = give_derive_campaign_id_from_form_id(999999);
        $this->assertEquals(0, $result, 'Should return 0 for form with no campaign');

        // Create a campaign
        $campaign = \Give\Campaigns\Models\Campaign::factory()->create([
            'goalType' => \Give\Campaigns\ValueObjects\CampaignGoalType::AMOUNT(),
        ]);

        // Get the form associated with the campaign
        $formId = $campaign->defaultFormId;

        // Test with a form that has a campaign - should return the campaign ID
        $result = give_derive_campaign_id_from_form_id($formId);
        $this->assertEquals($campaign->id, $result, 'Should return the correct campaign ID for form with campaign');

        // Test the filter hook
        add_filter('give_derive_campaign_id_from_form_id', function($derived_campaign_id, $form_id) {
            if ($form_id === 12345) {
                return 99999; // Override with custom value for test
            }
            return $derived_campaign_id;
        }, 10, 2);

        $filtered_result = give_derive_campaign_id_from_form_id(12345);
        $this->assertEquals(99999, $filtered_result, 'Filter should allow overriding the derived campaign ID');

        // Clean up filter
        remove_all_filters('give_derive_campaign_id_from_form_id');
    }

    /**
     * Test debugging the campaign ID setting process in add_payment method.
     *
     * @since 4.3.2
     */
    public function testAddPaymentCampaignIdDebugging()
    {
        // Create a campaign
        $campaign = \Give\Campaigns\Models\Campaign::factory()->create([
            'goalType' => \Give\Campaigns\ValueObjects\CampaignGoalType::AMOUNT(),
        ]);

        // Get the form associated with the campaign
        $formId = $campaign->defaultFormId;

        // Verify the utility function works first
        $derivedId = give_derive_campaign_id_from_form_id($formId);
        $this->assertEquals($campaign->id, $derivedId, 'Utility function should derive correct campaign ID');

        // Create a donor
        $donor = Donor::factory()->create();

        // Create a parent payment with the campaign form
        $parentPayment = new Give_Payment();
        $parentPayment->total = 100;
        $parentPayment->form_id = $formId;
        $parentPayment->customer_id = $donor->id;
        $parentPayment->first_name = $donor->firstName;
        $parentPayment->last_name = $donor->lastName;
        $parentPayment->email = $donor->email;
        $parentPayment->currency = 'USD';
        $parentPayment->status = 'publish';
        $parentPayment->gateway = TestGateway::id();
        $parentPayment->mode = give_is_test_mode() ? 'test' : 'live';
        $parentPayment->transaction_id = 'parent_debug_test';
        $parentPayment->save();

        // Create a subscription
        $subscription = new Give_Subscription();
        $subscriptionId = $subscription->create([
            'customer_id' => $donor->id,
            'parent_payment_id' => $parentPayment->ID,
            'form_id' => $formId,
            'period' => 'month',
            'frequency' => 1,
            'initial_amount' => 100,
            'recurring_amount' => 80,
            'status' => 'active',
        ]);
        $subscription = new Give_Subscription($subscriptionId);

        // Call add_payment to create a renewal payment
        $renewalArgs = [
            'amount' => 80,
            'transaction_id' => 'test_renewal_debug',
            'gateway' => TestGateway::id(),
        ];
        $result = $subscription->add_payment($renewalArgs);

        // Verify the payment was added successfully
        $this->assertTrue($result, 'Renewal payment should be created successfully');

        // Get the child payments (renewals)
        $childPayments = $subscription->get_child_payments();
        $this->assertCount(1, $childPayments, 'Should have one renewal payment');

        // Get the renewal payment object
        $renewalPayment = new Give_Payment($childPayments[0]->ID);

        // Debug: Check what campaign_id property shows
        $this->assertEquals($campaign->id, $renewalPayment->campaign_id, 'Campaign ID property should be set correctly');

        // Debug: Check if _give_campaign_id meta exists at all
        $allMeta = get_post_meta($renewalPayment->ID);
        $this->assertArrayHasKey('_give_campaign_id', $allMeta, '_give_campaign_id meta key should exist');

        // Debug: Check the meta value
        $savedCampaignId = give_get_meta($renewalPayment->ID, '_give_campaign_id', true);
        $this->assertEquals($campaign->id, $savedCampaignId, 'Campaign ID should be saved in payment meta');

        // Additional check: Make sure the meta value is actually the right type
        $this->assertIsNumeric($savedCampaignId, 'Campaign ID meta should be numeric');
        $this->assertGreaterThan(0, $savedCampaignId, 'Campaign ID meta should be greater than 0');
    }

    /**
     * Test that add_payment handles forms without campaigns correctly (should save 0 as campaign_id).
     *
     * @since 4.3.2
     */
    public function testAddPaymentWithFormWithoutCampaign()
    {
        // Use a form ID that doesn't have a campaign
        $formId = 9999;

        // Verify the utility function returns 0 for forms without campaigns
        $derivedId = give_derive_campaign_id_from_form_id($formId);
        $this->assertEquals(0, $derivedId, 'Utility function should return 0 for form without campaign');

        // Create a donor
        $donor = Donor::factory()->create();

        // Create a parent payment with the non-campaign form
        $parentPayment = new Give_Payment();
        $parentPayment->total = 100;
        $parentPayment->form_id = $formId;
        $parentPayment->customer_id = $donor->id;
        $parentPayment->first_name = $donor->firstName;
        $parentPayment->last_name = $donor->lastName;
        $parentPayment->email = $donor->email;
        $parentPayment->currency = 'USD';
        $parentPayment->status = 'publish';
        $parentPayment->gateway = TestGateway::id();
        $parentPayment->mode = give_is_test_mode() ? 'test' : 'live';
        $parentPayment->transaction_id = 'parent_no_campaign';
        $parentPayment->save();

        // Create a subscription
        $subscription = new Give_Subscription();
        $subscriptionId = $subscription->create([
            'customer_id' => $donor->id,
            'parent_payment_id' => $parentPayment->ID,
            'form_id' => $formId,
            'period' => 'month',
            'frequency' => 1,
            'initial_amount' => 100,
            'recurring_amount' => 80,
            'status' => 'active',
        ]);
        $subscription = new Give_Subscription($subscriptionId);

        // Call add_payment to create a renewal payment
        $renewalArgs = [
            'amount' => 80,
            'transaction_id' => 'test_renewal_no_campaign',
            'gateway' => TestGateway::id(),
        ];
        $result = $subscription->add_payment($renewalArgs);

        // Verify the payment was added successfully
        $this->assertTrue($result, 'Renewal payment should be created successfully');

        // Get the child payments (renewals)
        $childPayments = $subscription->get_child_payments();
        $this->assertCount(1, $childPayments, 'Should have one renewal payment');

        // Get the renewal payment object
        $renewalPayment = new Give_Payment($childPayments[0]->ID);

        // Campaign ID should be 0 for forms without campaigns
        $this->assertEquals(0, $renewalPayment->campaign_id, 'Campaign ID property should be 0 for form without campaign');

        // Verify the _give_campaign_id meta is set to 0
        $savedCampaignId = give_get_meta($renewalPayment->ID, '_give_campaign_id', true);
        $this->assertEquals(0, $savedCampaignId, 'Campaign ID meta should be 0 for form without campaign');

        // Verify meta exists (even if it's 0)
        $allMeta = get_post_meta($renewalPayment->ID);
        $this->assertArrayHasKey('_give_campaign_id', $allMeta, '_give_campaign_id meta should exist even when 0');
    }
}
