<?php

namespace Give\ServiceProviders;

use Give\Helpers\Hooks;
use Give\Milestones\Block as MilestoneBlock;

class Milestones implements ServiceProvider {

	/**
	 * @inheritDoc
	 */
	public function register() {
		give()->singleton( MilestoneBlock::class );
	}

	/**
	 * @inheritDoc
	 */
	public function boot() {
		Hooks::addAction( 'init', MilestoneBlock::class, 'addBlock' );
	}
}
