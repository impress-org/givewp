<?php

namespace Give\Form\LegacyConsumer;

class TemplateHooks {
	/**
	 * A "full-ish" list of available actions.
	 * @note The `give_` prefix has been removed for interoperability.
	 * @note Update to account for previously deprecated hooks.
	 * @link https://givewp.com/documentation/developers/how-to-create-custom-form-fields/
	 * @link https://givewp.com/add-content-donation-forms/
	 */
	const TEMPLATE_HOOKS = [
		'before_donation_levels',
		'after_donation_amount',
		'after_donation_levels',
		'payment_mode_top',
		'payment_mode_before_gateways',
		'payment_mode_after_gateways',
		'payment_mode_after_gateways_wrap',
		'payment_mode_bottom',
		'donation_form',
		'purchase_form_top',
		'donation_form_register_login_fields',
		'donation_form_before_cc_form',
		'cc_form',
		'before_cc_fields',
		'before_cc_expiration',
		'after_cc_expiration',
		'after_cc_fields',
		'donation_form_after_cc_form',
		'purchase_form_bottom',
	];

	public function walk( callable $callback ) {
		$hooks = $this->getHooks();
		array_walk( $hooks, $callback );
	}

	public function reduce( callable $callback, $initial = null ) {
		return array_reduce( $this->getHooks(), $callback, $initial );
	}

	public function getHooks() {
		return self::TEMPLATE_HOOKS;
	}
}
