<?php

namespace Give\Tests\Unit\Donors;

use Give\Donors\Actions\CreateUserFromDonor;
use Give\Donors\Models\Donor;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @since 4.19.1.1
 */
class DonorDefaultUserRoleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @since 4.19.1.1
     */
    public function testSafeRolesAreAccepted()
    {
        $this->assertTrue(give_is_donor_safe_user_role('give_donor'));
        $this->assertTrue(give_is_donor_safe_user_role('subscriber'));
    }

    /**
     * @since 4.19.1.1
     */
    public function testPrivilegedAndUnknownRolesAreRejected()
    {
        foreach (['administrator', 'editor', 'author', 'contributor', 'give_manager', 'give_accountant', 'missing_role', '', null, ['subscriber']] as $role) {
            $this->assertFalse(give_is_donor_safe_user_role($role), var_export($role, true));
        }
    }

    /**
     * @since 4.19.1.1
     */
    public function testStoredPrivilegedRoleFallsBackToDonorRole()
    {
        give_update_option('donor_default_user_role', 'administrator');

        $this->assertSame('administrator', give_get_option('donor_default_user_role'));
        $this->assertSame('give_donor', give_get_donor_default_user_role());
    }

    /**
     * @since 4.19.1.1
     */
    public function testStoredSafeRoleIsKept()
    {
        give_update_option('donor_default_user_role', 'subscriber');

        $this->assertSame('subscriber', give_get_donor_default_user_role());
    }

    /**
     * @since 4.19.1.1
     */
    public function testCustomRoleWithAdminCapabilityIsRejected()
    {
        add_role('give_test_custom_role', 'Custom', ['read' => true, 'activate_plugins' => true]);

        $this->assertFalse(give_is_donor_safe_user_role('give_test_custom_role'));

        remove_role('give_test_custom_role');
    }

    /**
     * @since 4.19.1.1
     */
    public function testNewDonorUserNeverGetsPrivilegedRole()
    {
        give_update_option('donor_default_user_role', 'editor');

        $donor = (new CreateUserFromDonor())(Donor::factory()->create(['userId' => 0]));

        $this->assertSame(['give_donor'], get_userdata($donor->userId)->roles);
    }
}
