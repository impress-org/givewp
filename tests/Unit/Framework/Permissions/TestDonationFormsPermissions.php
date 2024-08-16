<?php

namespace Give\Tests\Unit\Framework\Permissions;

use Give\Framework\Permissions\Facades\Permissions;
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
     * @dataProvider canProvider
     */
    public function testCan($role, $capability, $shouldPass): void
    {
        $user = self::factory()->user->create_and_get();
        $user->add_role($role);

        wp_set_current_user($user->ID);

        if ($shouldPass) {
            $this->assertTrue(
                Permissions::donationForms()->can($capability)
            );
        } else {
            $this->assertFalse(
                Permissions::donationForms()->can($capability)
            );
        }
    }


       /**
     * @unreleased
     *
     * @return array<int, array<mixed, bool>>
     */
    public function canProvider(): array
    {
        return [
            // true
            ['give_worker', 'create', true],
            ['give_worker', 'read', true],
            ['give_worker', 'update', true],
            ['give_worker', 'edit', true],

            ['give_manager', 'create', true],
            ['give_manager', 'read', true],
            ['give_manager', 'update', true],
            ['give_manager', 'edit', true],
            ['give_manager', 'delete', true],

            ['give_accountant', 'create', true],
            ['give_accountant', 'read', true],
            ['give_accountant', 'update', true],
            ['give_accountant', 'edit', true],
            ['give_accountant', 'read_private_give_forms', true],

            ['administrator', 'create', true],
            ['administrator', 'read', true],
            ['administrator', 'update', true],
            ['administrator', 'edit', true],
            ['administrator', 'delete', true],

            // false
            ['give_accountant', 'delete', false],

            ['give_donor', 'create', false],
            ['give_donor', 'read', false],
            ['give_donor', 'update', false],
            ['give_donor', 'edit', false],
            ['give_donor', 'delete', false],

            ['give_subscriber', 'create', false],
            ['give_subscriber', 'read', false],
            ['give_subscriber', 'update', false],
            ['give_subscriber', 'edit', false],
            ['give_subscriber', 'delete', false],

            ['subscriber', 'create', false],
            ['subscriber', 'read', false],
            ['subscriber', 'update', false],
            ['subscriber', 'edit', false],
            ['subscriber', 'delete', false],
        ];
    }
}
