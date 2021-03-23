<?php

namespace Give\DonorDashboards\Tabs\EditProfileTab;

use Give\DonorDashboards\Tabs\Contracts\Tab as TabAbstract;
use Give\DonorDashboards\Tabs\EditProfileTab\ProfileRoute;
use Give\DonorDashboards\Tabs\EditProfileTab\LocationRoute;
use Give\DonorDashboards\Tabs\EditProfileTab\AvatarRoute;

/**
 * @since 2.10.0
 */
class Tab extends TabAbstract {

	public static function id() {
		return 'edit-profile';
	}

	public function routes() {
		return [
			ProfileRoute::class,
			LocationRoute::class,
			AvatarRoute::class,
		];
	}
}
