<?php

namespace Give\Subscriptions\Models;

use Give_Recurring_Subscriber;

/**
 * Class LegacySubscriber
 *
 * @unreleased
 */
if (class_exists('Give_Recurring_Subscriber')){
	class LegacySubscriber extends Give_Recurring_Subscriber {

	}
} else {
	class LegacySubscriber {

	}
}