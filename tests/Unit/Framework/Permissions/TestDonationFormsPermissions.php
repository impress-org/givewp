<?php

namespace Give\Tests\Unit\Framework\Permissions;

use Give\Framework\Permissions\DonationFormPermissions;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @unreleased
 */
final class TestDonationFormsPermissions extends TestCase
{
    use RefreshDatabase;

    /**
     * @unreleased
     * @dataProvider canViewTrueProvider
     */
    public function testCanViewShouldBeTrue(string $role): void
    {
        $user = self::factory()->user->create_and_get();
        $user->set_role($role);

        wp_set_current_user($user->ID);

        $this->assertTrue(
            (new DonationFormPermissions())->canView()
        );
    }

    /**
     * @unreleased
     * @dataProvider canViewFalseProvider
     */
    public function testCanViewShouldBeFalse(string $role): void
    {
        $user = self::factory()->user->create_and_get();
        $user->set_role($role);

        wp_set_current_user($user->ID);

        $this->assertFalse(
            (new DonationFormPermissions())->canView()
        );
    }

    /**
     * @unreleased
     * @dataProvider canEditTrueProvider
     */
    public function testCanEditShouldBeTrue(string $role): void
    {
        $user = self::factory()->user->create_and_get();
        $user->set_role($role);

        wp_set_current_user($user->ID);

        $this->assertTrue(
            (new DonationFormPermissions())->canEdit()
        );
    }

    /**
     * @unreleased
     * @dataProvider canEditFalseProvider
     */
    public function testCanEditShouldBeFalse(string $role): void
    {
        $user = self::factory()->user->create_and_get();
        $user->set_role($role);

        wp_set_current_user($user->ID);

        $this->assertFalse(
            (new DonationFormPermissions())->canEdit()
        );
    }

    /**
     * @unreleased
     * @dataProvider canDeleteTrueProvider
     */
    public function testCanDeleteShouldBeTrue(string $role): void
    {
        $user = self::factory()->user->create_and_get();
        $user->set_role($role);

        wp_set_current_user($user->ID);

        $this->assertTrue(
            (new DonationFormPermissions())->canDelete()
        );
    }

    /**
     * @unreleased
     * @dataProvider canDeleteFalseProvider
     */
    public function testCanDeleteShouldBeFalse(string $role): void
    {
        $user = self::factory()->user->create_and_get();
        $user->set_role($role);

        wp_set_current_user($user->ID);

        $this->assertFalse(
            (new DonationFormPermissions())->canDelete()
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

        $permissions = new DonationFormPermissions();
        $this->assertTrue($permissions->canCreate());
        $this->assertTrue($permissions->canView());
        $this->assertTrue($permissions->canEdit());
        $this->assertTrue($permissions->canDelete());
    }

    public function canViewTrueProvider(): array
    {
        return [
            ['give_worker'],
            ['give_manager'],
            ['give_accountant'],
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

    public function canEditTrueProvider(): array
    {
        return [
            ['give_worker'],
            ['give_manager'],
            ['give_accountant'],
            ['administrator'],
        ];
    }

    public function canEditFalseProvider(): array
    {
        return [
            ['give_donor'],
            ['subscriber'],
        ];
    }

    public function canDeleteTrueProvider(): array
    {
        return [
            ['give_manager'],
            ['give_worker'],  // give_worker has delete_give_forms capability
            ['administrator'],
        ];
    }

    public function canDeleteFalseProvider(): array
    {
        return [
            ['give_accountant'],
            ['give_donor'],
            ['subscriber'],
        ];
    }
}
