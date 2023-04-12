<?php

namespace Give\Tests\TestTraits;

use WP_UnitTest_Factory;

trait HasDefaultWordPressUsers
{
    /**
     * Creates a set of default WordPress users with different roles.
     *
     * @since 2.26.0
     *
     * @param WP_UnitTest_Factory $factory
     *
     * @return array
     */
    public static function createDefaultWordPressUsers(WP_UnitTest_Factory $factory): array
    {
        $roles = [
            'administrator',
            'editor',
            'author',
            'contributor',
            'subscriber',
        ];

        $users = [];
        foreach ( $roles as $role ) {
            $users[$role] = $factory->user->create(['role' => $role]);
        }

        return $users;
    }
}
