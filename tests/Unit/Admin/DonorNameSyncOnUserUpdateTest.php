<?php

namespace Give\Tests\Unit\Admin;

use Give\Donors\Models\Donor;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @since TBD
 */
final class DonorNameSyncOnUserUpdateTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @since TBD
     */
    public function setUp(): void
    {
        parent::setUp();

        unset($_POST['first_name'], $_POST['last_name']);

        if (!function_exists('give_update_donor_name_on_user_update')) {
            require_once GIVE_PLUGIN_DIR . 'includes/admin/admin-actions.php';
        }
    }

    /**
     * @since TBD
     */
    public function testDonorNameDropsTagsFromTheUserName(): void
    {
        $donorId = $this->syncName('<b>Ann</b>', '<i>Lee</i>');

        $this->assertSame('Ann Lee', Donor::find($donorId)->name);
    }

    /**
     * @since TBD
     */
    public function testDonorNameHasNoLeadingSpaceWhenTheFirstNameIsOnlyMarkup(): void
    {
        $donorId = $this->syncName('<b></b>', 'Lee');

        $this->assertSame('Lee', Donor::find($donorId)->name);
    }

    /**
     * Saves the names as user meta, then runs the profile update sync as an admin.
     *
     * @since TBD
     */
    private function syncName(string $firstName, string $lastName): int
    {
        $adminId = self::factory()->user->create(['role' => 'administrator']);
        $userId = self::factory()->user->create(['role' => 'subscriber']);

        update_user_meta($userId, 'first_name', $firstName);
        update_user_meta($userId, 'last_name', $lastName);

        /** @var Donor $donor */
        $donor = Donor::factory()->create(['userId' => $userId]);

        wp_set_current_user($adminId);
        give_update_donor_name_on_user_update($userId);

        return $donor->id;
    }
}
