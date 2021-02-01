<?php

namespace Give\DonorProfiles\Tabs\DonationHistoryTab;

use Give\DonorProfiles\Tabs\Contracts\Tab as TabAbstract;
use Give\DonorProfiles\Tabs\DonationHistoryTab\DonationsRoute;

class Tab extends TabAbstract {

	public static function id() {
		return 'donation-history';
	}

	public function routes() {
		return [
			DonationsRoute::class,
		];
	}
}
