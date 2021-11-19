<?php

namespace Give\DonorDashboards\Tabs\EditProfileTab;

use Give\DonorDashboards\Tabs\Contracts\Tab as TabAbstract;

/**
 * @since 2.10.0
 */
class Tab extends TabAbstract
{

    public static function id()
    {
        return 'edit-profile';
    }

    public function routes()
    {
        return [
            ProfileRoute::class,
            LocationRoute::class,
            AvatarRoute::class,
        ];
    }
}
