<?php

namespace Give\MultiFormGoals;

use Give\ServiceProviders\ServiceProvider as ServiceProviderInterface;
use Give\Helpers\Hooks;
use Give\MultiFormGoals\MultiFormGoal\Shortcode as MultiFormGoalShortcode;
use Give\MultiFormGoals\MultiFormGoal\Block as MultiFormGoalBlock;
use Give\MultiFormGoals\ProgressBar\Block as ProgressBarBlock;

class ServiceProvider implements ServiceProviderInterface {

	/**
	 * @inheritDoc
	 */
	public function register() {
		give()->singleton( MultiFormGoalShortcode::class );
		give()->singleton( MultiFormGoalBlock::class );
		give()->singleton( ProgressBarBlock::class );
	}

	/**
	 * @inheritDoc
	 */
	public function boot() {
		Hooks::addAction( 'init', MultiFormGoalShortcode::class, 'addShortcode' );
		Hooks::addAction( 'init', MultiFormGoalBlock::class, 'addBlock' );
		Hooks::addAction( 'init', ProgressBarBlock::class, 'addBlock' );
		Hooks::addAction( 'enqueue_block_editor_assets', ProgressBarBlock::class, 'localizeAssets' );
	}
}
