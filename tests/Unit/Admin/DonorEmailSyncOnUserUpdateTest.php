<?php

namespace Give\Tests\Unit\Admin;

use Give\Donors\Models\Donor;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @since TBD
 */
final class DonorEmailSyncOnUserUpdateTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @since TBD
     */
    public function setUp(): void
    {
        parent::setUp();

        if (!function_exists('give_update_donor_email_on_user_update')) {
            require_once GIVE_PLUGIN_DIR . 'includes/admin/admin-actions.php';
        }
    }

    /**
     * @since TBD
     */
    public function testNonAdminCannotSyncNewEmailToDonor(): void
    {
        $user = self::factory()->user->create_and_get([
            'role' => 'subscriber',
            'user_email' => 'sync-new-primary@test.com',
        ]);

        /** @var Donor $donor */
        $donor = Donor::factory()->create([
            'userId' => $user->ID,
            'email' => 'sync-new-primary@test.com',
        ]);

        wp_set_current_user($user->ID);

        wp_update_user([
            'ID' => $user->ID,
            'user_email' => 'sync-new-changed@test.com',
        ]);

        give_update_donor_email_on_user_update($user->ID);

        $this->assertEquals('sync-new-primary@test.com', Donor::find($donor->id)->email);
    }

    /**
     * @since TBD
     */
    public function testNonAdminCanSyncExistingAdditionalEmailToDonor(): void
    {
        $user = self::factory()->user->create_and_get([
            'role' => 'subscriber',
            'user_email' => 'sync-existing-primary@test.com',
        ]);

        /** @var Donor $donor */
        $donor = Donor::factory()->create([
            'userId' => $user->ID,
            'email' => 'sync-existing-primary@test.com',
            'additionalEmails' => ['sync-existing-additional@test.com'],
        ]);

        wp_set_current_user($user->ID);

        wp_update_user([
            'ID' => $user->ID,
            'user_email' => 'sync-existing-additional@test.com',
        ]);

        give_update_donor_email_on_user_update($user->ID);

        $this->assertEquals('sync-existing-additional@test.com', Donor::find($donor->id)->email);
    }

    /**
     * @since TBD
     */
    public function testAdminCanSyncNewEmailToDonor(): void
    {
        $admin = self::factory()->user->create_and_get([
            'role' => 'administrator',
        ]);

        $user = self::factory()->user->create_and_get([
            'role' => 'subscriber',
            'user_email' => 'sync-admin-primary@test.com',
        ]);

        /** @var Donor $donor */
        $donor = Donor::factory()->create([
            'userId' => $user->ID,
            'email' => 'sync-admin-primary@test.com',
        ]);

        wp_set_current_user($admin->ID);

        wp_update_user([
            'ID' => $user->ID,
            'user_email' => 'sync-admin-changed@test.com',
        ]);

        give_update_donor_email_on_user_update($user->ID);

        $this->assertEquals('sync-admin-changed@test.com', Donor::find($donor->id)->email);
    }
}
