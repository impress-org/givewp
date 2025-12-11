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
     * @dataProvider canTrueProvider
     */
    public function testCanShouldBeTrue(string $role, string $capability): void
    {
        $user = self::factory()->user->create_and_get();
        $user->set_role($role);

        wp_set_current_user($user->ID);

        $this->assertTrue(
            (new ReportsPermissions())->can($capability)
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
            (new ReportsPermissions())->can($capability)
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
            ['administrator', 'export'],

            ['give_manager', 'view'],
            ['give_manager', 'read'],
            ['give_manager', 'export'],

            ['give_accountant', 'view'],
            ['give_accountant', 'read'],
            ['give_accountant', 'export'],
        ];
    }

    /**
     * @unreleased
     */
    public function canFalseProvider(): array
    {
        return [
            ['give_worker', 'view'],
            ['give_worker', 'read'],
            ['give_worker', 'export'],

            ['give_donor', 'view'],
            ['give_donor', 'read'],
            ['give_donor', 'export'],

            ['subscriber', 'view'],
            ['subscriber', 'read'],
            ['subscriber', 'export'],
        ];
    }
}
