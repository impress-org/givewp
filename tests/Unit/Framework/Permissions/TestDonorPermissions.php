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
     * Roles with view_give_reports can view donors.
     *
     * @unreleased
     * @dataProvider canViewTrueProvider
     */
    public function testCanViewShouldBeTrue(string $role): void
    {
        $user = self::factory()->user->create_and_get();
        $user->set_role($role);

        wp_set_current_user($user->ID);

        $this->assertTrue(
            (new DonorPermissions())->canView()
        );
    }

    /**
     * Roles without view_give_reports cannot view donors.
     * give_worker can view donations but NOT donors.
     *
     * @unreleased
     * @dataProvider canViewFalseProvider
     */
    public function testCanViewShouldBeFalse(string $role): void
    {
        $user = self::factory()->user->create_and_get();
        $user->set_role($role);

        wp_set_current_user($user->ID);

        $this->assertFalse(
            (new DonorPermissions())->canView()
        );
    }

    /**
     * Roles with edit_give_payments can edit donors.
     *
     * @unreleased
     * @dataProvider canEditTrueProvider
     */
    public function testCanEditShouldBeTrue(string $role): void
    {
        $user = self::factory()->user->create_and_get();
        $user->set_role($role);

        wp_set_current_user($user->ID);

        $this->assertTrue(
            (new DonorPermissions())->canEdit()
        );
    }

    /**
     * Roles without edit_give_payments cannot edit donors.
     *
     * @unreleased
     * @dataProvider canEditFalseProvider
     */
    public function testCanEditShouldBeFalse(string $role): void
    {
        $user = self::factory()->user->create_and_get();
        $user->set_role($role);

        wp_set_current_user($user->ID);

        $this->assertFalse(
            (new DonorPermissions())->canEdit()
        );
    }

    /**
     * Roles with delete_give_payments can delete donors.
     *
     * @unreleased
     * @dataProvider canDeleteTrueProvider
     */
    public function testCanDeleteShouldBeTrue(string $role): void
    {
        $user = self::factory()->user->create_and_get();
        $user->set_role($role);

        wp_set_current_user($user->ID);

        $this->assertTrue(
            (new DonorPermissions())->canDelete()
        );
    }

    /**
     * Roles without delete_give_payments cannot delete donors.
     *
     * @unreleased
     * @dataProvider canDeleteFalseProvider
     */
    public function testCanDeleteShouldBeFalse(string $role): void
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
     * Roles that have view_give_reports capability.
     * give_manager, give_accountant have this; give_worker does NOT.
     */
    public function canViewTrueProvider(): array
    {
        return [
            ['give_manager'],
            ['give_accountant'],
            ['administrator'],
        ];
    }

    /**
     * Roles that do NOT have view_give_reports capability.
     * give_worker can view donations but NOT donors.
     */
    public function canViewFalseProvider(): array
    {
        return [
            ['give_worker'],
            ['give_donor'],
            ['subscriber'],
        ];
    }

    /**
     * Roles that have edit_give_payments capability.
     */
    public function canEditTrueProvider(): array
    {
        return [
            ['give_manager'],
            ['give_accountant'],
            ['administrator'],
        ];
    }

    /**
     * Roles that do NOT have edit_give_payments capability.
     * Note: give_worker does NOT have edit_give_payments after the migration fix.
     */
    public function canEditFalseProvider(): array
    {
        return [
            ['give_worker'],
            ['give_donor'],
            ['subscriber'],
        ];
    }

    /**
     * Delete maps to edit for donors (inherited from DonationPermissions).
     * Roles that have edit_give_payments capability can delete.
     */
    public function canDeleteTrueProvider(): array
    {
        return [
            ['give_manager'],
            ['give_accountant'],
            ['administrator'],
        ];
    }

    /**
     * Delete maps to edit for donors (inherited from DonationPermissions).
     * Roles that do NOT have edit_give_payments capability cannot delete.
     */
    public function canDeleteFalseProvider(): array
    {
        return [
            ['give_worker'],
            ['give_donor'],
            ['subscriber'],
        ];
    }
}

