<?php

namespace Give\Subscriptions\Models;

use Give_Recurring_Subscriber;

/**
 * Class LegacySubscriber
 *
 * This is a temporary class that extends a give-recurring concept of Give_Recurring_Subscriber.
 * To avoid any issue with someone trying to use this without having give-recurring installed,
 * there is a conditional to make sure the extended class exists.
 *
 * @since 2.18.0
 */
if (class_exists('Give_Recurring_Subscriber')){
	class LegacySubscriber extends Give_Recurring_Subscriber {

	}
} else {
	class LegacySubscriber {

	}
}
