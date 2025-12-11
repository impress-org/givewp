<?php

namespace Give\Tests\Unit\Framework\Permissions;

use Give\Framework\Permissions\SensitiveDataPermissions;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @unreleased
 */
final class TestSensitiveDataPermissions extends TestCase
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
            (new SensitiveDataPermissions())->can($capability)
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
            (new SensitiveDataPermissions())->can($capability)
        );
    }

    /**
     * @unreleased
     *
     * @return array<int, array<mixed, bool>>
     */
    public function canTrueProvider(): array
    {
        return [
            ['administrator', 'view'],
            ['administrator', 'read'],

            ['give_manager', 'view'],
            ['give_manager', 'read'],
        ];
    }

    /**
     * @unreleased
     */
    public function canFalseProvider(): array
    {
        return [
            // give_accountant does NOT have view_give_sensitive_data
            ['give_accountant', 'view'],
            ['give_accountant', 'read'],

            ['give_worker', 'view'],
            ['give_worker', 'read'],

            ['give_donor', 'view'],
            ['give_donor', 'read'],

            ['subscriber', 'view'],
            ['subscriber', 'read'],
        ];
    }
}
