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
     * @dataProvider canTrueProvider
     */
    public function testCanShouldBeTrue(string $role, string $capability): void
    {
        $user = self::factory()->user->create_and_get();
        $user->set_role($role);

        wp_set_current_user($user->ID);

        $this->assertTrue(
            (new SettingsPermissions())->can($capability)
        );
    }

    /**
     * @unreleased
     * @dataProvider canFalseProvider
     */
    public function testCanShouldBeFalse(string $role, string $capability): void
    {
        $user = self::factory()->user->create_and_get();
        $user->set_role($role);

        wp_set_current_user($user->ID);

        $this->assertFalse(
            (new SettingsPermissions())->can($capability)
        );
    }

    /**
     * Users with manage_options capability should always have full access.
     *
     * @unreleased
     * @dataProvider adminOverrideProvider
     */
    public function testAdminWithManageOptionsAlwaysReturnsTrue(string $capability): void
    {
        $user = self::factory()->user->create_and_get();
        $user->set_role('administrator');

        wp_set_current_user($user->ID);

        // Verify user has manage_options
        $this->assertTrue(current_user_can('manage_options'));

        $this->assertTrue(
            (new SettingsPermissions())->can($capability)
        );
    }

    /**
     * @unreleased
     */
    public function adminOverrideProvider(): array
    {
        return [
            ['manage'],
            ['edit'],
            ['update'],
        ];
    }

    /**
     * @unreleased
     *
     * @return array<int, array<mixed, bool>>
     */
    public function canTrueProvider(): array
    {
        return [
            ['administrator', 'manage'],
            ['administrator', 'edit'],
            ['administrator', 'update'],

            ['give_manager', 'manage'],
            ['give_manager', 'edit'],
            ['give_manager', 'update'],
        ];
    }

    /**
     * @unreleased
     */
    public function canFalseProvider(): array
    {
        return [
            // give_accountant does NOT have manage_give_settings
            ['give_accountant', 'manage'],
            ['give_accountant', 'edit'],
            ['give_accountant', 'update'],

            ['give_worker', 'manage'],
            ['give_worker', 'edit'],
            ['give_worker', 'update'],

            ['give_donor', 'manage'],
            ['give_donor', 'edit'],
            ['give_donor', 'update'],

            ['subscriber', 'manage'],
            ['subscriber', 'edit'],
            ['subscriber', 'update'],
        ];
    }
}
