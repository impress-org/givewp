<?php

namespace Give\Tests\Unit\Framework\Permissions;

use Give\Framework\Permissions\SettingsPermissions;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @unreleased
 */
final class TestSettingsPermissions extends TestCase
{
    use RefreshDatabase;

    /**
     * @unreleased
     * @dataProvider canManageTrueProvider
     */
    public function testCanManageShouldBeTrue(string $role): void
    {
        $user = self::factory()->user->create_and_get();
        $user->set_role($role);

        wp_set_current_user($user->ID);

        $this->assertTrue(
            (new SettingsPermissions())->canManage()
        );
    }

    /**
     * @unreleased
     * @dataProvider canManageFalseProvider
     */
    public function testCanManageShouldBeFalse(string $role): void
    {
        $user = self::factory()->user->create_and_get();
        $user->set_role($role);

        wp_set_current_user($user->ID);

        $this->assertFalse(
            (new SettingsPermissions())->canManage()
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

        $permissions = new SettingsPermissions();
        $this->assertTrue($permissions->canManage());
    }

    public function canManageTrueProvider(): array
    {
        return [
            ['administrator'],
            ['give_manager'],
        ];
    }

    public function canManageFalseProvider(): array
    {
        return [
            ['give_accountant'],
            ['give_worker'],
            ['give_donor'],
            ['subscriber'],
        ];
    }
}
