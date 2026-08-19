<?php

namespace Give\Tests\Unit\Framework\Permissions;

use Give\Framework\Permissions\SensitiveDataPermissions;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @since 4.14.0
 */
final class TestSensitiveDataPermissions extends TestCase
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
            (new SensitiveDataPermissions())->canView()
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
            (new SensitiveDataPermissions())->canView()
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

        $permissions = new SensitiveDataPermissions();
        $this->assertTrue($permissions->canView());
    }

    public function canViewTrueProvider(): array
    {
        return [
            ['administrator'],
            ['give_manager'],
        ];
    }

    public function canViewFalseProvider(): array
    {
        return [
            ['give_accountant'],
            ['give_worker'],
            ['give_donor'],
            ['subscriber'],
        ];
    }
}
