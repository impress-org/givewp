<?php

namespace Give\Tests\Unit\Framework\Permissions;

use Give\Framework\Permissions\DonationPermissions;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @since 4.14.0
 */
final class TestDonationPermissions extends TestCase
{
    use RefreshDatabase;

    /**
     * @since 4.14.0
     * @dataProvider canViewTrueProvider
     */
    public function testCanViewShouldBeTrue(string $role): void
    {
        $user = self::factory()->user->create_and_get();
        $user->set_role($role);

        wp_set_current_user($user->ID);

        $this->assertTrue(
            (new DonationPermissions())->canView()
        );
    }

    /**
     * @since 4.14.0
     * @dataProvider canViewFalseProvider
     */
    public function testCanViewShouldBeFalse(string $role): void
    {
        $user = self::factory()->user->create_and_get();
        $user->set_role($role);

        wp_set_current_user($user->ID);

        $this->assertFalse(
            (new DonationPermissions())->canView()
        );
    }

    /**
     * @since 4.14.0
     * @dataProvider canEditTrueProvider
     */
    public function testCanEditShouldBeTrue(string $role): void
    {
        $user = self::factory()->user->create_and_get();
        $user->set_role($role);

        wp_set_current_user($user->ID);

        $this->assertTrue(
            (new DonationPermissions())->canEdit()
        );
    }

    /**
     * @since 4.14.0
     * @dataProvider canEditFalseProvider
     */
    public function testCanEditShouldBeFalse(string $role): void
    {
        $user = self::factory()->user->create_and_get();
        $user->set_role($role);

        wp_set_current_user($user->ID);

        $this->assertFalse(
            (new DonationPermissions())->canEdit()
        );
    }

    /**
     * @since 4.14.0
     * @dataProvider canDeleteTrueProvider
     */
    public function testCanDeleteShouldBeTrue(string $role): void
    {
        $user = self::factory()->user->create_and_get();
        $user->set_role($role);

        wp_set_current_user($user->ID);

        $this->assertTrue(
            (new DonationPermissions())->canDelete()
        );
    }

    /**
     * @since 4.14.0
     * @dataProvider canDeleteFalseProvider
     */
    public function testCanDeleteShouldBeFalse(string $role): void
    {
        $user = self::factory()->user->create_and_get();
        $user->set_role($role);

        wp_set_current_user($user->ID);

        $this->assertFalse(
            (new DonationPermissions())->canDelete()
        );
    }

    /**
     * @since 4.14.0
     */
    public function testAdminWithManageOptionsAlwaysReturnsTrue(): void
    {
        $user = self::factory()->user->create_and_get();
        $user->set_role('administrator');

        wp_set_current_user($user->ID);

        $this->assertTrue(current_user_can('manage_options'));

        $permissions = new DonationPermissions();
        $this->assertTrue($permissions->canCreate());
        $this->assertTrue($permissions->canView());
        $this->assertTrue($permissions->canEdit());
        $this->assertTrue($permissions->canDelete());
    }

    /**
     * Donations use view_give_payments for canView.
     * give_manager, give_accountant, give_worker, and administrator have view_give_payments.
     */
    public function canViewTrueProvider(): array
    {
        return [
            ['give_manager'],
            ['give_accountant'],
            ['give_worker'],
            ['administrator'],
        ];
    }

    public function canViewFalseProvider(): array
    {
        return [
            ['give_donor'],
            ['subscriber'],
        ];
    }

    /**
     * Donations use edit_give_payments for canEdit.
     * give_worker does NOT have edit_give_payments (can only view, not edit donations).
     */
    public function canEditTrueProvider(): array
    {
        return [
            ['give_manager'],
            ['give_accountant'],
            ['administrator'],
        ];
    }

    public function canEditFalseProvider(): array
    {
        return [
            ['give_worker'],
            ['give_donor'],
            ['subscriber'],
        ];
    }

    /**
     * Delete maps to edit for donations.
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
     * Delete maps to edit for donations.
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
