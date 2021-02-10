<?php

namespace Give\Tracking\ValueObjects;

/**
 * Class EventId
 * @package Give\Tracking\ValueObjects
 *
 * @since 2.10.0
 */
class EventId {

	const CREATE_TOKEN            = 'create-token';
	const PLUGIN_SETTINGS_UPDATED = 'plugin-settings-updated';
	const THEME_SWITCHED          = 'theme-switched';
	const THEME_UPDATED           = 'theme-updated';
	const PLUGIN_LIST_UPDATED     = 'plugin-list-updated';
	const DONATION_METRICS        = 'donation-metrics';
	const DONATION_FORM_UPDATED   = 'donation-form-updated';
}
