<?php

namespace Give\DonorProfiles\Tabs\ProfileTab;

use Give\DonorProfiles\Tabs\Contracts\Tab as TabAbstract;
use Give\DonorProfiles\Tabs\ProfileTab\ProfileRoute;
use Give\DonorProfiles\Tabs\ProfileTab\LocationRoute;

/**
 * @since 2.10.0
 */
class Tab extends TabAbstract {

	public static function id() {
		return 'profile';
	}

	public function routes() {
		return [
			ProfileRoute::class,
			LocationRoute::class,
		];
	}
}
