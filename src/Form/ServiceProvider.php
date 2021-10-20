<?php

namespace Give\Form;

use Give\Form\Migrations\MoveOptionsToVisualAppearanceSection;
use Give\Helpers\Hooks;

/**
 * @unreleased
 */
class ServiceProvider implements \Give\ServiceProviders\ServiceProvider {

	/**
	 * @inheritDoc
	 */
	public function register() {

	}

	/**
	 * @inheritDoc
	 */
	public function boot() {
		Hooks::addAction( 'give_register_updates', MoveOptionsToVisualAppearanceSection::class, 'register' );
	}
}
