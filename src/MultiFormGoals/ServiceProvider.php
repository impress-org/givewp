<?php

namespace Give\MultiFormGoals;

use Give\ServiceProviders\ServiceProvider as ServiceProviderInterface;
use Give\Helpers\Hooks;
use Give\MultiFormGoals\Block as MultiFormGoalsBlock;

class ServiceProvider implements ServiceProviderInterface {

	/**
	 * @inheritDoc
	 */
	public function register() {
		give()->singleton( MultiFormGoalsBlock::class );
	}

	/**
	 * @inheritDoc
	 */
	public function boot() {
		Hooks::addAction( 'init', MultiFormGoalsBlock::class, 'addBlock' );
	}
}
