<?php

namespace Give\Tests\Unit\Framework\Permissions;

use Give\Framework\Permissions\ReportsPermissions;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @unreleased
 */
final class TestReportsPermissions extends TestCase
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
            (new ReportsPermissions())->canView()
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
            (new ReportsPermissions())->canView()
        );
    }

    /**
     * @unreleased
     * @dataProvider canExportTrueProvider
     */
    public function testCanExportShouldBeTrue(string $role): void
    {
        $user = self::factory()->user->create_and_get();
        $user->set_role($role);

        wp_set_current_user($user->ID);

        $this->assertTrue(
            (new ReportsPermissions())->canExport()
        );
    }

    /**
     * @unreleased
     * @dataProvider canExportFalseProvider
     */
    public function testCanExportShouldBeFalse(string $role): void
    {
        $user = self::factory()->user->create_and_get();
        $user->set_role($role);

        wp_set_current_user($user->ID);

        $this->assertFalse(
            (new ReportsPermissions())->canExport()
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

        $permissions = new ReportsPermissions();
        $this->assertTrue($permissions->canView());
        $this->assertTrue($permissions->canExport());
    }

    public function canViewTrueProvider(): array
    {
        return [
            ['administrator'],
            ['give_manager'],
            ['give_accountant'],
        ];
    }

    public function canViewFalseProvider(): array
    {
        return [
            ['give_worker'],
            ['give_donor'],
            ['subscriber'],
        ];
    }

    public function canExportTrueProvider(): array
    {
        return [
            ['administrator'],
            ['give_manager'],
            ['give_accountant'],
        ];
    }

    public function canExportFalseProvider(): array
    {
        return [
            ['give_worker'],
            ['give_donor'],
            ['subscriber'],
        ];
    }
}
