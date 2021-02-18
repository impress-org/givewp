<?php

namespace Give\DonorProfiles\Tabs\EditProfileTab;

use Give\DonorProfiles\Tabs\Contracts\Tab as TabAbstract;
use Give\DonorProfiles\Tabs\ProfileTab\ProfileRoute;
use Give\DonorProfiles\Tabs\ProfileTab\LocationRoute;

class Tab extends TabAbstract {

	public static function id() {
		return 'edit-profile';
	}

	public function routes() {
		return [
			ProfileRoute::class,
			LocationRoute::class,
		];
	}
}
