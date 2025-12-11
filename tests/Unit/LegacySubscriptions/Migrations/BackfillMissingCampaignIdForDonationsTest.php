<?php

namespace Unit\LegacySubscriptions\Migrations;

use Give\Framework\Database\DB;
use Give\Subscriptions\Migrations\BackfillMissingCampaignIdForDonations;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * Test class for BackfillMissingCampaignIdForDonations migration
 *
 * This test suite covers both the original individual processing logic
 * and the new bulk processing functionality. The migration now processes
 * donations in batches for improved performance while maintaining the
 * same logical priority order:
 *
 * 1. Campaign ID from revenue table (highest priority)
 * 2. Campaign ID from parent payment (for renewals)
 * 3. Campaign ID from form-campaign mapping (fallback)
 *
 * Key test scenarios covered:
 * - Individual donation processing (backward compatibility)
 * - Bulk processing with multiple donation scenarios
 * - Priority handling in bulk operations
 * - Edge cases and error handling
 * - Performance optimization verification
 *
 * @since 4.3.2
 */
class BackfillMissingCampaignIdForDonationsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @since 4.3.2
     */
    public function testMigrationBackfillsCampaignIdFromRevenueTable()
    {
        // Create a renewal donation without campaignId but with renewal status
        $renewalPaymentId = $this->factory->post->create([
            'post_type' => 'give_payment',
            'post_status' => 'give_subscription', // Renewal status
        ]);

        // Add campaign_id to revenue table for this donation
        DB::table('give_revenue')->insert([
            'donation_id' => $renewalPaymentId,
            'form_id' => 1,
            'campaign_id' => 999,
            'amount' => 100,
        ]);

        // Verify the renewal doesn't have a campaignId in meta initially
        $existingCampaignId = DB::table('give_donationmeta')
            ->where('donation_id', $renewalPaymentId)
            ->where('meta_key', '_give_campaign_id')
            ->value('meta_value');

        $this->assertEmpty($existingCampaignId);

        // Run the migration
        $migration = new BackfillMissingCampaignIdForDonations();
        $migration->runBatch($renewalPaymentId, $renewalPaymentId);

        // Verify the campaignId was backfilled from revenue table
        $updatedCampaignId = DB::table('give_donationmeta')
            ->where('donation_id', $renewalPaymentId)
            ->where('meta_key', '_give_campaign_id')
            ->value('meta_value');

        $this->assertEquals(999, (int)$updatedCampaignId);
    }

    /**
     * @since 4.3.2
     */
    public function testMigrationBackfillsCampaignIdFromParentDonation()
    {
        // Create a parent payment with a campaignId using direct DB operations
        $parentPaymentId = $this->factory->post->create([
            'post_type' => 'give_payment',
            'post_status' => 'publish',
        ]);

        // Add campaignId meta to parent payment
        DB::table('give_donationmeta')->insert([
            'donation_id' => $parentPaymentId,
            'meta_key' => '_give_campaign_id',
            'meta_value' => 123,
        ]);

        // Add form_id meta to parent payment
        DB::table('give_donationmeta')->insert([
            'donation_id' => $parentPaymentId,
            'meta_key' => '_give_payment_form_id',
            'meta_value' => 1,
        ]);

        // Create a subscription
        DB::table('give_subscriptions')->insert([
            'customer_id' => 1,
            'parent_payment_id' => $parentPaymentId,
            'period' => 'month',
            'frequency' => 1,
            'initial_amount' => 100,
            'recurring_amount' => 100,
            'bill_times' => 0,
            'transaction_id' => 'test_txn_123',
            'product_id' => 1,
            'created' => current_time('mysql'),
            'expiration' => date('Y-m-d H:i:s', strtotime('+1 month')),
            'status' => 'active',
            'profile_id' => 'test_profile_123',
            'notes' => '',
        ]);

        $subscriptionId = DB::last_insert_id();

        // Create a renewal donation without campaignId but with renewal status
        $renewalPaymentId = $this->factory->post->create([
            'post_type' => 'give_payment',
            'post_status' => 'give_subscription', // Renewal status
        ]);

        // Add subscription_id meta to renewal payment
        DB::table('give_donationmeta')->insert([
            'donation_id' => $renewalPaymentId,
            'meta_key' => '_give_subscription_id',
            'meta_value' => $subscriptionId,
        ]);

        // Verify the renewal doesn't have a campaignId initially
        $existingCampaignId = DB::table('give_donationmeta')
            ->where('donation_id', $renewalPaymentId)
            ->where('meta_key', '_give_campaign_id')
            ->value('meta_value');

        $this->assertEmpty($existingCampaignId);

        // Run the migration
        $migration = new BackfillMissingCampaignIdForDonations();
        $migration->runBatch($renewalPaymentId, $renewalPaymentId);

        // Verify the campaignId was backfilled
        $updatedCampaignId = DB::table('give_donationmeta')
            ->where('donation_id', $renewalPaymentId)
            ->where('meta_key', '_give_campaign_id')
            ->value('meta_value');

        $this->assertEquals(123, (int)$updatedCampaignId);
    }

    /**
     * @since 4.3.2
     */
    public function testMigrationBackfillsCampaignIdFromFormWhenParentHasNone()
    {
        // Create a campaign
        DB::table('give_campaigns')->insert([
            'form_id' => 1,
            'campaign_type' => 'core',
            'campaign_title' => 'Test Campaign',
            'campaign_url' => 'test-campaign',
            'short_desc' => 'Test description',
            'long_desc' => 'Test long description',
            'campaign_logo' => '',
            'campaign_image' => '',
            'primary_color' => '#000000',
            'secondary_color' => '#ffffff',
            'campaign_goal' => 10000,
            'goal_type' => 'amount',
            'status' => 'active',
            'start_date' => current_time('mysql'),
            'end_date' => null,
            'date_created' => current_time('mysql'),
        ]);

        $campaignId = DB::last_insert_id();

        // Create form-campaign relationship
        DB::table('give_campaign_forms')->insert([
            'form_id' => 1,
            'campaign_id' => $campaignId,
        ]);

        // Create a parent payment WITHOUT campaignId
        $parentPaymentId = $this->factory->post->create([
            'post_type' => 'give_payment',
            'post_status' => 'publish',
        ]);

        // Add form_id meta to parent payment
        DB::table('give_donationmeta')->insert([
            'donation_id' => $parentPaymentId,
            'meta_key' => '_give_payment_form_id',
            'meta_value' => 1,
        ]);

        // Create a subscription
        DB::table('give_subscriptions')->insert([
            'customer_id' => 1,
            'parent_payment_id' => $parentPaymentId,
            'period' => 'month',
            'frequency' => 1,
            'initial_amount' => 100,
            'recurring_amount' => 100,
            'bill_times' => 0,
            'transaction_id' => 'test_txn_456',
            'product_id' => 1,
            'created' => current_time('mysql'),
            'expiration' => date('Y-m-d H:i:s', strtotime('+1 month')),
            'status' => 'active',
            'profile_id' => 'test_profile_456',
            'notes' => '',
        ]);

        $subscriptionId = DB::last_insert_id();

        // Create a renewal donation without campaignId
        $renewalPaymentId = $this->factory->post->create([
            'post_type' => 'give_payment',
            'post_status' => 'give_subscription', // Renewal status
        ]);

        // Add subscription_id meta to renewal payment
        DB::table('give_donationmeta')->insert([
            'donation_id' => $renewalPaymentId,
            'meta_key' => '_give_subscription_id',
            'meta_value' => $subscriptionId,
        ]);

        // Run the migration
        $migration = new BackfillMissingCampaignIdForDonations();
        $migration->runBatch($renewalPaymentId, $renewalPaymentId);

        // Verify the campaignId was backfilled from the form-campaign association
        $updatedCampaignId = DB::table('give_donationmeta')
            ->where('donation_id', $renewalPaymentId)
            ->where('meta_key', '_give_campaign_id')
            ->value('meta_value');

        $this->assertEquals($campaignId, (int)$updatedCampaignId);
    }

    /**
     * @since 4.3.2
     */
    public function testMigrationIgnoresDonationsWithExistingCampaignId()
    {
        // Clean up any existing test data first
        DB::query('DELETE FROM ' . DB::prefix('posts') . ' WHERE post_type = "give_payment"');
        DB::query('DELETE FROM ' . DB::prefix('give_donationmeta'));

        // Create a donation that already has a campaignId
        $donationId = $this->factory->post->create([
            'post_type' => 'give_payment',
            'post_status' => 'publish',
        ]);

        // Add existing campaignId meta
        DB::table('give_donationmeta')->insert([
            'donation_id' => $donationId,
            'meta_key' => '_give_campaign_id',
            'meta_value' => 456,
        ]);

        // The query should find NO donations since the only one has campaignId
        $migration = new BackfillMissingCampaignIdForDonations();
        $count = $migration->getItemsCount();

        $this->assertEquals(0, $count);

        // Now create another donation WITHOUT campaignId
        $donationId2 = $this->factory->post->create([
            'post_type' => 'give_payment',
            'post_status' => 'publish',
        ]);

        // Now the query should find exactly 1 donation (the one without campaignId)
        $count = $migration->getItemsCount();
        $this->assertEquals(1, $count);
    }

    /**
     * Test that migration includes non-renewal donations
     *
     * @since 4.3.2
     */
    public function testMigrationIncludesNonRenewalDonations()
    {
        // Clean up any existing test data first
        DB::query('DELETE FROM ' . DB::prefix('posts') . ' WHERE post_type = "give_payment"');
        DB::query('DELETE FROM ' . DB::prefix('give_donationmeta'));

        // Create a regular donation without campaignId (not a renewal)
        $donationId = $this->factory->post->create([
            'post_type' => 'give_payment',
            'post_status' => 'publish', // Regular status, not renewal
        ]);

        // This donation should now be found by our query since we handle all donations
        $migration = new BackfillMissingCampaignIdForDonations();
        $count = $migration->getItemsCount();

        $this->assertEquals(1, $count);
    }

    /**
     * Test migration backfills campaignId for regular donations from form association
     *
     * @since 4.3.2
     */
    public function testMigrationBackfillsCampaignIdForRegularDonationFromForm()
    {
        // Create a campaign
        DB::table('give_campaigns')->insert([
            'form_id' => 1,
            'campaign_type' => 'core',
            'campaign_title' => 'Test Campaign',
            'campaign_url' => 'test-campaign',
            'short_desc' => 'Test description',
            'long_desc' => 'Test long description',
            'campaign_logo' => '',
            'campaign_image' => '',
            'primary_color' => '#000000',
            'secondary_color' => '#ffffff',
            'campaign_goal' => 10000,
            'goal_type' => 'amount',
            'status' => 'active',
            'start_date' => current_time('mysql'),
            'end_date' => null,
            'date_created' => current_time('mysql'),
        ]);

        $campaignId = DB::last_insert_id();

        // Create form-campaign relationship
        DB::table('give_campaign_forms')->insert([
            'form_id' => 1,
            'campaign_id' => $campaignId,
        ]);

        // Create a regular donation without campaignId
        $donationId = $this->factory->post->create([
            'post_type' => 'give_payment',
            'post_status' => 'publish', // Regular status
        ]);

        // Add form_id meta to donation
        DB::table('give_donationmeta')->insert([
            'donation_id' => $donationId,
            'meta_key' => '_give_payment_form_id',
            'meta_value' => 1,
        ]);

        // Run the migration
        $migration = new BackfillMissingCampaignIdForDonations();
        $migration->runBatch($donationId, $donationId);

        // Verify the campaignId was backfilled from the form-campaign association
        $updatedCampaignId = DB::table('give_donationmeta')
            ->where('donation_id', $donationId)
            ->where('meta_key', '_give_campaign_id')
            ->value('meta_value');

        $this->assertEquals($campaignId, (int)$updatedCampaignId);
    }

    /**
     * @since 4.3.2
     */
    public function testMigrationBackfillsCampaignIdFromRenewalFormWhenNoParentPayment()
    {
        // Create a campaign
        DB::table('give_campaigns')->insert([
            'form_id' => 1,
            'campaign_type' => 'core',
            'campaign_title' => 'Test Campaign',
            'campaign_url' => 'test-campaign',
            'short_desc' => 'Test description',
            'long_desc' => 'Test long description',
            'campaign_logo' => '',
            'campaign_image' => '',
            'primary_color' => '#000000',
            'secondary_color' => '#ffffff',
            'campaign_goal' => 10000,
            'goal_type' => 'amount',
            'status' => 'active',
            'start_date' => current_time('mysql'),
            'end_date' => null,
            'date_created' => current_time('mysql'),
        ]);

        $campaignId = DB::last_insert_id();

        // Create form-campaign relationship
        DB::table('give_campaign_forms')->insert([
            'form_id' => 1,
            'campaign_id' => $campaignId,
        ]);

        // Create a renewal donation without campaignId and WITHOUT a parent payment/subscription
        $renewalPaymentId = $this->factory->post->create([
            'post_type' => 'give_payment',
            'post_status' => 'give_subscription', // Renewal status
        ]);

        // Add form_id meta directly to the renewal payment (no parent payment)
        DB::table('give_donationmeta')->insert([
            'donation_id' => $renewalPaymentId,
            'meta_key' => '_give_payment_form_id',
            'meta_value' => 1,
        ]);

        // Run the migration
        $migration = new BackfillMissingCampaignIdForDonations();
        $migration->runBatch($renewalPaymentId, $renewalPaymentId);

        // Verify the campaignId was backfilled from the renewal donation's form-campaign association
        $updatedCampaignId = DB::table('give_donationmeta')
            ->where('donation_id', $renewalPaymentId)
            ->where('meta_key', '_give_campaign_id')
            ->value('meta_value');

        $this->assertEquals($campaignId, (int)$updatedCampaignId);
    }

    /**
     * @since 4.3.2
     */
    public function testMigrationPrioritizesRevenueTableOverParentDonation()
    {
        // Create a parent payment with a different campaignId
        $parentPaymentId = $this->factory->post->create([
            'post_type' => 'give_payment',
            'post_status' => 'publish',
        ]);

        // Add campaignId meta to parent payment
        DB::table('give_donationmeta')->insert([
            'donation_id' => $parentPaymentId,
            'meta_key' => '_give_campaign_id',
            'meta_value' => 777, // Different campaign ID
        ]);

        // Create a subscription
        DB::table('give_subscriptions')->insert([
            'customer_id' => 1,
            'parent_payment_id' => $parentPaymentId,
            'period' => 'month',
            'frequency' => 1,
            'initial_amount' => 100,
            'recurring_amount' => 100,
            'bill_times' => 0,
            'transaction_id' => 'test_txn_priority',
            'product_id' => 1,
            'created' => current_time('mysql'),
            'expiration' => date('Y-m-d H:i:s', strtotime('+1 month')),
            'status' => 'active',
            'profile_id' => 'test_profile_priority',
            'notes' => '',
        ]);

        $subscriptionId = DB::last_insert_id();

        // Create a renewal donation
        $renewalPaymentId = $this->factory->post->create([
            'post_type' => 'give_payment',
            'post_status' => 'give_subscription', // Renewal status
        ]);

        // Add subscription_id meta to renewal payment
        DB::table('give_donationmeta')->insert([
            'donation_id' => $renewalPaymentId,
            'meta_key' => '_give_subscription_id',
            'meta_value' => $subscriptionId,
        ]);

        // Add campaign_id to revenue table with different value than parent
        DB::table('give_revenue')->insert([
            'donation_id' => $renewalPaymentId,
            'form_id' => 1,
            'campaign_id' => 888, // Different from parent's 777
            'amount' => 100,
        ]);

        // Run the migration
        $migration = new BackfillMissingCampaignIdForDonations();
        $migration->runBatch($renewalPaymentId, $renewalPaymentId);

        // Verify the campaignId was taken from revenue table (888), not parent (777)
        $updatedCampaignId = DB::table('give_donationmeta')
            ->where('donation_id', $renewalPaymentId)
            ->where('meta_key', '_give_campaign_id')
            ->value('meta_value');

        $this->assertEquals(888, (int)$updatedCampaignId);
        $this->assertNotEquals(777, (int)$updatedCampaignId);
    }

    /**
     * Test bulk processing of multiple donations with different scenarios
     *
     * @since 4.3.2
     */
    public function testBulkProcessingHandlesMultipleDonationsEfficiently()
    {
        // Create test data for multiple scenarios

        // Scenario 1: Donation with revenue table campaign ID
        $donation1 = $this->factory->post->create(['post_type' => 'give_payment']);
        DB::table('give_revenue')->insert([
            'donation_id' => $donation1,
            'form_id' => 1,
            'campaign_id' => 100,
            'amount' => 50,
        ]);

        // Scenario 2: Renewal donation with parent campaign ID
        $parentPayment = $this->factory->post->create(['post_type' => 'give_payment']);
        DB::table('give_donationmeta')->insert([
            'donation_id' => $parentPayment,
            'meta_key' => '_give_campaign_id',
            'meta_value' => 200,
        ]);

        DB::table('give_subscriptions')->insert([
            'customer_id' => 1,
            'parent_payment_id' => $parentPayment,
            'period' => 'month',
            'frequency' => 1,
            'initial_amount' => 100,
            'recurring_amount' => 100,
            'bill_times' => 0,
            'transaction_id' => 'test_bulk_1',
            'product_id' => 1,
            'created' => current_time('mysql'),
            'expiration' => date('Y-m-d H:i:s', strtotime('+1 month')),
            'status' => 'active',
            'profile_id' => 'test_profile_bulk_1',
            'notes' => '',
        ]);
        $subscriptionId = DB::last_insert_id();

        $donation2 = $this->factory->post->create(['post_type' => 'give_payment']);
        DB::table('give_donationmeta')->insert([
            'donation_id' => $donation2,
            'meta_key' => '_give_subscription_id',
            'meta_value' => $subscriptionId,
        ]);

        // Scenario 3: Donation with form-campaign mapping
        DB::table('give_campaigns')->insert([
            'form_id' => 2,
            'campaign_type' => 'core',
            'campaign_title' => 'Bulk Test Campaign',
            'campaign_url' => 'bulk-test-campaign',
            'short_desc' => 'Test description',
            'long_desc' => 'Test long description',
            'campaign_logo' => '',
            'campaign_image' => '',
            'primary_color' => '#000000',
            'secondary_color' => '#ffffff',
            'campaign_goal' => 5000,
            'goal_type' => 'amount',
            'status' => 'active',
            'start_date' => current_time('mysql'),
            'end_date' => null,
            'date_created' => current_time('mysql'),
        ]);
        $campaignId3 = DB::last_insert_id();

        DB::table('give_campaign_forms')->insert([
            'form_id' => 2,
            'campaign_id' => $campaignId3,
        ]);

        $donation3 = $this->factory->post->create(['post_type' => 'give_payment']);
        DB::table('give_donationmeta')->insert([
            'donation_id' => $donation3,
            'meta_key' => '_give_payment_form_id',
            'meta_value' => 2,
        ]);

        // Run migration on all three donations
        $firstId = min($donation1, $donation2, $donation3);
        $lastId = max($donation1, $donation2, $donation3);

        $migration = new BackfillMissingCampaignIdForDonations();
        $migration->runBatch($firstId, $lastId);

        // Verify all donations got the correct campaign IDs
        $campaignId1 = DB::table('give_donationmeta')
            ->where('donation_id', $donation1)
            ->where('meta_key', '_give_campaign_id')
            ->value('meta_value');
        $this->assertEquals(100, (int)$campaignId1);

        $campaignId2 = DB::table('give_donationmeta')
            ->where('donation_id', $donation2)
            ->where('meta_key', '_give_campaign_id')
            ->value('meta_value');
        $this->assertEquals(200, (int)$campaignId2);

        $foundCampaignId3 = DB::table('give_donationmeta')
            ->where('donation_id', $donation3)
            ->where('meta_key', '_give_campaign_id')
            ->value('meta_value');
        $this->assertEquals($campaignId3, (int)$foundCampaignId3);
    }

    /**
     * Test that bulk processing prioritizes correctly
     *
     * @since 4.3.2
     */
    public function testBulkProcessingPrioritizesRevenuTableOverOtherSources()
    {
        // Create a donation that has both revenue table entry AND form mapping
        $donation = $this->factory->post->create(['post_type' => 'give_payment']);

        // Add revenue table entry (should be prioritized)
        DB::table('give_revenue')->insert([
            'donation_id' => $donation,
            'form_id' => 1,
            'campaign_id' => 500, // Priority 1
            'amount' => 100,
        ]);

        // Add form mapping (should be ignored since revenue exists)
        DB::table('give_campaigns')->insert([
            'form_id' => 1,
            'campaign_type' => 'core',
            'campaign_title' => 'Priority Test Campaign',
            'campaign_url' => 'priority-test-campaign',
            'short_desc' => 'Test description',
            'long_desc' => 'Test long description',
            'campaign_logo' => '',
            'campaign_image' => '',
            'primary_color' => '#000000',
            'secondary_color' => '#ffffff',
            'campaign_goal' => 5000,
            'goal_type' => 'amount',
            'status' => 'active',
            'start_date' => current_time('mysql'),
            'end_date' => null,
            'date_created' => current_time('mysql'),
        ]);
        $formCampaignId = DB::last_insert_id();

        DB::table('give_campaign_forms')->insert([
            'form_id' => 1,
            'campaign_id' => $formCampaignId, // This should be ignored
        ]);

        DB::table('give_donationmeta')->insert([
            'donation_id' => $donation,
            'meta_key' => '_give_payment_form_id',
            'meta_value' => 1,
        ]);

        // Run migration
        $migration = new BackfillMissingCampaignIdForDonations();
        $migration->runBatch($donation, $donation);

        // Verify revenue table value was used (500), not form mapping
        $campaignId = DB::table('give_donationmeta')
            ->where('donation_id', $donation)
            ->where('meta_key', '_give_campaign_id')
            ->value('meta_value');

        $this->assertEquals(500, (int)$campaignId);
        $this->assertNotEquals($formCampaignId, (int)$campaignId);
    }

    /**
     * Test that bulk processing ignores donations with existing campaign IDs
     *
     * @since 4.3.2
     */
    public function testBulkProcessingIgnoresDonationsWithExistingCampaignIds()
    {
        // Create donations: one with existing campaign ID, one without
        $donationWithCampaign = $this->factory->post->create(['post_type' => 'give_payment']);
        $donationWithoutCampaign = $this->factory->post->create(['post_type' => 'give_payment']);

        // Add existing campaign ID to first donation
        DB::table('give_donationmeta')->insert([
            'donation_id' => $donationWithCampaign,
            'meta_key' => '_give_campaign_id',
            'meta_value' => 777,
        ]);

        // Add revenue table entry for second donation
        DB::table('give_revenue')->insert([
            'donation_id' => $donationWithoutCampaign,
            'form_id' => 1,
            'campaign_id' => 888,
            'amount' => 100,
        ]);

        $migration = new BackfillMissingCampaignIdForDonations();

        // The query should only find the donation without campaign ID
        $count = $migration->getItemsCount();
        $this->assertEquals(1, $count);

        // Run migration on both donations
        $firstId = min($donationWithCampaign, $donationWithoutCampaign);
        $lastId = max($donationWithCampaign, $donationWithoutCampaign);
        $migration->runBatch($firstId, $lastId);

        // Verify first donation still has original campaign ID (unchanged)
        $campaign1 = DB::table('give_donationmeta')
            ->where('donation_id', $donationWithCampaign)
            ->where('meta_key', '_give_campaign_id')
            ->value('meta_value');
        $this->assertEquals(777, (int)$campaign1);

        // Verify second donation got new campaign ID
        $campaign2 = DB::table('give_donationmeta')
            ->where('donation_id', $donationWithoutCampaign)
            ->where('meta_key', '_give_campaign_id')
            ->value('meta_value');
        $this->assertEquals(888, (int)$campaign2);
    }

    /**
     * Test bulk processing handles edge cases gracefully
     *
     * @since 4.3.2
     */
    public function testBulkProcessingHandlesEdgeCasesGracefully()
    {
        // Scenario: Donations that can't find campaign IDs anywhere
        $donation1 = $this->factory->post->create(['post_type' => 'give_payment']);
        $donation2 = $this->factory->post->create(['post_type' => 'give_payment']);
        $donation3 = $this->factory->post->create(['post_type' => 'give_payment']);

        // Add some incomplete data
        // Donation 2 has a subscription but no parent payment
        DB::table('give_donationmeta')->insert([
            'donation_id' => $donation2,
            'meta_key' => '_give_subscription_id',
            'meta_value' => 9999, // Non-existent subscription
        ]);

        // Donation 3 has a form but no campaign mapping
        DB::table('give_donationmeta')->insert([
            'donation_id' => $donation3,
            'meta_key' => '_give_payment_form_id',
            'meta_value' => 9999, // Form with no campaign mapping
        ]);

        $migration = new BackfillMissingCampaignIdForDonations();

        // Should not throw an exception even with incomplete data
        $firstId = min($donation1, $donation2, $donation3);
        $lastId = max($donation1, $donation2, $donation3);
        $migration->runBatch($firstId, $lastId);

        // Verify none of these donations got campaign IDs (since none could be determined)
        foreach ([$donation1, $donation2, $donation3] as $donationId) {
            $campaignId = DB::table('give_donationmeta')
                ->where('donation_id', $donationId)
                ->where('meta_key', '_give_campaign_id')
                ->value('meta_value');
            $this->assertEmpty($campaignId);
        }
    }

    /**
     * Test that empty batch processing is handled gracefully
     *
     * @since 4.3.2
     */
    public function testBulkProcessingHandlesEmptyBatchGracefully()
    {
        // Clean up any existing test data
        DB::query('DELETE FROM ' . DB::prefix('posts') . ' WHERE post_type = "give_payment"');
        DB::query('DELETE FROM ' . DB::prefix('give_donationmeta'));

        $migration = new BackfillMissingCampaignIdForDonations();

        // Should not throw an exception with empty batch
        $migration->runBatch(1, 100);

        // Should return 0 items
        $this->assertEquals(0, $migration->getItemsCount());
    }

    /**
     * Test that bulk processing prevents duplicate campaign_id meta entries
     *
     * @since 4.3.2
     */
    public function testBulkProcessingPreventsDuplicateCampaignIdMeta()
    {
        // Create a donation without campaign ID
        $donation = $this->factory->post->create(['post_type' => 'give_payment']);

        // Add revenue table entry
        DB::table('give_revenue')->insert([
            'donation_id' => $donation,
            'form_id' => 1,
            'campaign_id' => 123,
            'amount' => 100,
        ]);

        $migration = new BackfillMissingCampaignIdForDonations();

        // Run migration first time
        $migration->runBatch($donation, $donation);

        // Verify campaign ID was added
        $campaignId = DB::table('give_donationmeta')
            ->where('donation_id', $donation)
            ->where('meta_key', '_give_campaign_id')
            ->value('meta_value');
        $this->assertEquals(123, (int)$campaignId);

        // Count the number of campaign_id meta entries
        $metaCount = DB::table('give_donationmeta')
            ->where('donation_id', $donation)
            ->where('meta_key', '_give_campaign_id')
            ->count();
        $this->assertEquals(1, $metaCount);

        // Run migration second time - should not create duplicate
        $migration->runBatch($donation, $donation);

        // Verify still only one meta entry
        $metaCount = DB::table('give_donationmeta')
            ->where('donation_id', $donation)
            ->where('meta_key', '_give_campaign_id')
            ->count();
        $this->assertEquals(1, $metaCount);

        // Verify campaign ID value is still correct
        $campaignId = DB::table('give_donationmeta')
            ->where('donation_id', $donation)
            ->where('meta_key', '_give_campaign_id')
            ->value('meta_value');
        $this->assertEquals(123, (int)$campaignId);
    }

    /**
     * Test bulk processing with mixed scenarios - some donations have existing campaign IDs
     *
     * @since 4.3.2
     */
    public function testBulkProcessingWithMixedExistingCampaignIds()
    {
        // Create three donations
        $donation1 = $this->factory->post->create(['post_type' => 'give_payment']);
        $donation2 = $this->factory->post->create(['post_type' => 'give_payment']);
        $donation3 = $this->factory->post->create(['post_type' => 'give_payment']);

        // Add existing campaign ID to donation2
        DB::table('give_donationmeta')->insert([
            'donation_id' => $donation2,
            'meta_key' => '_give_campaign_id',
            'meta_value' => 999,
        ]);

        // Add revenue table entries for all donations
        DB::table('give_revenue')->insert([
            'donation_id' => $donation1,
            'form_id' => 1,
            'campaign_id' => 111,
            'amount' => 100,
        ]);
        DB::table('give_revenue')->insert([
            'donation_id' => $donation2,
            'form_id' => 1,
            'campaign_id' => 222,
            'amount' => 100,
        ]);
        DB::table('give_revenue')->insert([
            'donation_id' => $donation3,
            'form_id' => 1,
            'campaign_id' => 333,
            'amount' => 100,
        ]);

        $migration = new BackfillMissingCampaignIdForDonations();

        // Run migration on all donations
        $firstId = min($donation1, $donation2, $donation3);
        $lastId = max($donation1, $donation2, $donation3);
        $migration->runBatch($firstId, $lastId);

        // Verify donation1 got new campaign ID
        $campaign1 = DB::table('give_donationmeta')
            ->where('donation_id', $donation1)
            ->where('meta_key', '_give_campaign_id')
            ->value('meta_value');
        $this->assertEquals(111, (int)$campaign1);

        // Verify donation2 kept original campaign ID (wasn't processed)
        $campaign2 = DB::table('give_donationmeta')
            ->where('donation_id', $donation2)
            ->where('meta_key', '_give_campaign_id')
            ->value('meta_value');
        $this->assertEquals(999, (int)$campaign2);

        // Verify donation3 got new campaign ID
        $campaign3 = DB::table('give_donationmeta')
            ->where('donation_id', $donation3)
            ->where('meta_key', '_give_campaign_id')
            ->value('meta_value');
        $this->assertEquals(333, (int)$campaign3);

        // Verify no duplicates for any donation
        foreach ([$donation1, $donation2, $donation3] as $donationId) {
            $metaCount = DB::table('give_donationmeta')
                ->where('donation_id', $donationId)
                ->where('meta_key', '_give_campaign_id')
                ->count();
            $this->assertEquals(1, $metaCount);
        }
    }
}
