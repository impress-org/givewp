<?php

namespace Give\Tests\Unit\Framework\Permissions;

use Give\Framework\Permissions\DonorPermissions;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @unreleased
 */
final class TestDonorPermissions extends TestCase
{
    use RefreshDatabase;

    /**
     * Note: GiveWP does not define donor-specific capabilities (edit_give_donors, delete_give_donors, etc.)
     * in the Give_Roles class. Only administrators have access via the isAdmin() bypass.
     *
     * @unreleased
     * @dataProvider nonAdminRolesProvider
     */
    public function testNonAdminRolesCannotViewDonors(string $role): void
    {
        $user = self::factory()->user->create_and_get();
        $user->set_role($role);

        wp_set_current_user($user->ID);

        $this->assertFalse(
            (new DonorPermissions())->canView()
        );
    }

    /**
     * @unreleased
     * @dataProvider nonAdminRolesProvider
     */
    public function testNonAdminRolesCannotEditDonors(string $role): void
    {
        $user = self::factory()->user->create_and_get();
        $user->set_role($role);

        wp_set_current_user($user->ID);

        $this->assertFalse(
            (new DonorPermissions())->canEdit()
        );
    }

    /**
     * @unreleased
     * @dataProvider nonAdminRolesProvider
     */
    public function testNonAdminRolesCannotDeleteDonors(string $role): void
    {
        $user = self::factory()->user->create_and_get();
        $user->set_role($role);

        wp_set_current_user($user->ID);

        $this->assertFalse(
            (new DonorPermissions())->canDelete()
        );
    }

    /**
     * @unreleased
     */
    public function testAdminWithManageOptionsAlwaysReturnsTrue(): void
    {
        $user = self::factory()->user->create_and_get();
        $user->set_role('administrator');

        wp_set_current_user($user->ID);

        $this->assertTrue(current_user_can('manage_options'));

        $permissions = new DonorPermissions();
        $this->assertTrue($permissions->canCreate());
        $this->assertTrue($permissions->canView());
        $this->assertTrue($permissions->canEdit());
        $this->assertTrue($permissions->canDelete());
    }

    /**
     * Non-admin roles do not have donor-specific capabilities defined.
     */
    public function nonAdminRolesProvider(): array
    {
        return [
            ['give_worker'],
            ['give_manager'],
            ['give_accountant'],
            ['give_donor'],
            ['subscriber'],
        ];
    }
}

