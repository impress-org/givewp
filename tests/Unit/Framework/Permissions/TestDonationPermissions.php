<?php

namespace Give\Tests\Unit\Framework\Permissions;

use Give\Framework\Permissions\DonationFormPermissions;
use Give\Framework\Permissions\DonationPermissions;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @unreleased
 */
final class TestDonationPermissions extends TestCase
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
            (new DonationPermissions())->can($capability)
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
            (new DonationPermissions())->can($capability)
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
            // true
            ['give_worker', 'create'],
            ['give_worker', 'read'],
            ['give_worker', 'update'],
            ['give_worker', 'edit'],

            ['give_manager', 'create'],
            ['give_manager', 'read'],
            ['give_manager', 'update'],
            ['give_manager', 'edit'],
            ['give_manager', 'delete'],

            ['give_accountant', 'create'],
            ['give_accountant', 'read'],
            ['give_accountant', 'update'],
            ['give_accountant', 'edit'],

            ['administrator', 'create'],
            ['administrator', 'read'],
            ['administrator', 'update'],
            ['administrator', 'edit'],
            ['administrator', 'delete'],
        ];
    }

    /**
     * @unreleased
     */
    public function canFalseProvider(): array
    {
        return [
            // false
            ['give_accountant', 'delete'],

            ['give_donor', 'create'],
            ['give_donor', 'read'],
            ['give_donor', 'update'],
            ['give_donor', 'edit'],
            ['give_donor', 'delete'],

            ['give_subscriber', 'create'],
            ['give_subscriber', 'read'],
            ['give_subscriber', 'update'],
            ['give_subscriber', 'edit'],
            ['give_subscriber', 'delete'],

            ['subscriber', 'create'],
            ['subscriber', 'read'],
            ['subscriber', 'update'],
            ['subscriber', 'edit'],
            ['subscriber', 'delete'],
        ];
    }
}
