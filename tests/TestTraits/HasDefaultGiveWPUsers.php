<?php

namespace Give\Tests\TestTraits;

use WP_UnitTest_Factory;

trait HasDefaultGiveWPUsers
{
    /**
     * Creates a set of default GiveWP users with different roles.
     *
     * @since 2.26.0
     *
     * @param WP_UnitTest_Factory $factory
     *
     * @return array
     */
    public static function createDefaultGiveWPUsers(WP_UnitTest_Factory $factory): array
    {
        $roles = [
            'give_manager',
            'give_accountant',
            'give_worker',
            'give_donor',
            'give_subscriber',
        ];

        $users = [];
        foreach ( $roles as $role ) {
            $users[$role] = $factory->user->create(['role' => $role]);
        }

        return $users;
    }
}
