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
            (new DonationPermissions())->can($capability)
        );
    }

    /**
     * @unreleased
     */
    public function adminOverrideProvider(): array
    {
        return [
            ['create'],
            ['read'],
            ['view'],
            ['update'],
            ['edit'],
            ['delete'],
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
            // give_worker has edit_give_payments but NOT view_give_payments
            ['give_worker', 'create'],
            ['give_worker', 'update'],
            ['give_worker', 'edit'],

            // give_manager has both edit_give_payments and view_give_payments
            ['give_manager', 'create'],
            ['give_manager', 'view'],
            ['give_manager', 'read'],
            ['give_manager', 'update'],
            ['give_manager', 'edit'],
            ['give_manager', 'delete'],

            // give_accountant has edit_give_payments and view_give_payments
            ['give_accountant', 'create'],
            ['give_accountant', 'view'],
            ['give_accountant', 'read'],
            ['give_accountant', 'update'],
            ['give_accountant', 'edit'],

            // administrator has all capabilities
            ['administrator', 'create'],
            ['administrator', 'view'],
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
            // give_worker does NOT have view_give_payments
            ['give_worker', 'view'],
            ['give_worker', 'read'],

            ['give_accountant', 'delete'],

            ['give_donor', 'create'],
            ['give_donor', 'view'],
            ['give_donor', 'read'],
            ['give_donor', 'update'],
            ['give_donor', 'edit'],
            ['give_donor', 'delete'],

            ['give_subscriber', 'create'],
            ['give_subscriber', 'view'],
            ['give_subscriber', 'read'],
            ['give_subscriber', 'update'],
            ['give_subscriber', 'edit'],
            ['give_subscriber', 'delete'],

            ['subscriber', 'create'],
            ['subscriber', 'view'],
            ['subscriber', 'read'],
            ['subscriber', 'update'],
            ['subscriber', 'edit'],
            ['subscriber', 'delete'],
        ];
    }
}
