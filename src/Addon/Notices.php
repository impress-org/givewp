<?php

namespace Give\Addon;

/**
 * Helper class responsible for showing add-on notices.
 *
 * @package     Give\Addon\Helpers
 * @copyright   Copyright (c) 2020, GiveWP
 */
class Notices
{

    /**
     * GiveWP min required version notice.
     *
     * @since 0.1.0
     * @return void
     */
    public static function giveVersionError()
    {
        Give()->notices->register_notice(
            [
                'id' => 'give-next-gen-activation-error',
                'type' => 'error',
                'description' => View::load('Addon.admin/notices/give-version-error'),
                'show' => true,
            ]
        );
    }

    /**
     * GiveWP inactive notice.
     *
     * @since 0.1.0
     * @return void
     */
    public static function giveInactive()
    {
        echo View::load('Addon.admin/notices/give-inactive');
    }
}
