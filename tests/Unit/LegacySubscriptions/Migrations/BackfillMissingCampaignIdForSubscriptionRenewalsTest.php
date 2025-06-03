<?php

namespace Unit\LegacySubscriptions\Migrations;

use Give\Framework\Database\DB;
use Give\Subscriptions\Migrations\BackfillMissingCampaignIdForSubscriptionRenewals;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @unreleased
 */
class BackfillMissingCampaignIdForSubscriptionRenewalsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @unreleased
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
        $migration = new BackfillMissingCampaignIdForSubscriptionRenewals();
        $migration->runBatch($renewalPaymentId, $renewalPaymentId);

        // Verify the campaignId was backfilled from revenue table
        $updatedCampaignId = DB::table('give_donationmeta')
            ->where('donation_id', $renewalPaymentId)
            ->where('meta_key', '_give_campaign_id')
            ->value('meta_value');

        $this->assertEquals(999, (int)$updatedCampaignId);
    }

    /**
     * @unreleased
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
        $migration = new BackfillMissingCampaignIdForSubscriptionRenewals();
        $migration->runBatch($renewalPaymentId, $renewalPaymentId);

        // Verify the campaignId was backfilled
        $updatedCampaignId = DB::table('give_donationmeta')
            ->where('donation_id', $renewalPaymentId)
            ->where('meta_key', '_give_campaign_id')
            ->value('meta_value');

        $this->assertEquals(123, (int)$updatedCampaignId);
    }

    /**
     * @unreleased
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
        $migration = new BackfillMissingCampaignIdForSubscriptionRenewals();
        $migration->runBatch($renewalPaymentId, $renewalPaymentId);

        // Verify the campaignId was backfilled from the form-campaign association
        $updatedCampaignId = DB::table('give_donationmeta')
            ->where('donation_id', $renewalPaymentId)
            ->where('meta_key', '_give_campaign_id')
            ->value('meta_value');

        $this->assertEquals($campaignId, (int)$updatedCampaignId);
    }

    /**
     * @unreleased
     */
    public function testMigrationIgnoresDonationsWithExistingCampaignId()
    {
        // Clean up any existing test data first
        DB::query('DELETE FROM ' . DB::prefix('posts') . ' WHERE post_type = "give_payment"');
        DB::query('DELETE FROM ' . DB::prefix('give_donationmeta'));

        // Create a renewal donation that already has a campaignId
        $renewalPaymentId = $this->factory->post->create([
            'post_type' => 'give_payment',
            'post_status' => 'give_subscription', // Renewal status
        ]);

        // Add existing campaignId meta
        DB::table('give_donationmeta')->insert([
            'donation_id' => $renewalPaymentId,
            'meta_key' => '_give_campaign_id',
            'meta_value' => 456,
        ]);

        // The query should find NO donations since the only one has campaignId
        $migration = new BackfillMissingCampaignIdForSubscriptionRenewals();
        $count = $migration->getItemsCount();

        $this->assertEquals(0, $count);

        // Now create another renewal donation WITHOUT campaignId
        $renewalPaymentId2 = $this->factory->post->create([
            'post_type' => 'give_payment',
            'post_status' => 'give_subscription', // Renewal status
        ]);

        // Now the query should find exactly 1 donation (the one without campaignId)
        $count = $migration->getItemsCount();
        $this->assertEquals(1, $count);
    }

    /**
     * @unreleased
     */
    public function testMigrationIgnoresNonRenewalDonations()
    {
        // Clean up any existing test data first
        DB::query('DELETE FROM ' . DB::prefix('posts') . ' WHERE post_type = "give_payment"');
        DB::query('DELETE FROM ' . DB::prefix('give_donationmeta'));

        // Create a regular donation without campaignId (not a renewal)
        $donationId = $this->factory->post->create([
            'post_type' => 'give_payment',
            'post_status' => 'publish', // Regular status, not renewal
        ]);

        // This donation should not be found by our query since it's not a renewal
        $migration = new BackfillMissingCampaignIdForSubscriptionRenewals();
        $count = $migration->getItemsCount();

        $this->assertEquals(0, $count);
    }

    /**
     * @unreleased
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
        $migration = new BackfillMissingCampaignIdForSubscriptionRenewals();
        $migration->runBatch($renewalPaymentId, $renewalPaymentId);

        // Verify the campaignId was backfilled from the renewal donation's form-campaign association
        $updatedCampaignId = DB::table('give_donationmeta')
            ->where('donation_id', $renewalPaymentId)
            ->where('meta_key', '_give_campaign_id')
            ->value('meta_value');

        $this->assertEquals($campaignId, (int)$updatedCampaignId);
    }

    /**
     * @unreleased
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
        $migration = new BackfillMissingCampaignIdForSubscriptionRenewals();
        $migration->runBatch($renewalPaymentId, $renewalPaymentId);

        // Verify the campaignId was taken from revenue table (888), not parent (777)
        $updatedCampaignId = DB::table('give_donationmeta')
            ->where('donation_id', $renewalPaymentId)
            ->where('meta_key', '_give_campaign_id')
            ->value('meta_value');

        $this->assertEquals(888, (int)$updatedCampaignId);
        $this->assertNotEquals(777, (int)$updatedCampaignId);
    }
}
