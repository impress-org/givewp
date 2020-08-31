<?php

namespace Give\ServiceProviders;

use Give\Helpers\Hooks;
use Give\Milestones\Block\Block as MilestoneBlock;

class Milestones implements ServiceProvider {
	public function register() {
		give()->singleton( MilestoneBlock::class );
	}
	public function boot() {
		Hooks::addAction( 'init', MilestoneBlock::class, 'add_block' );
		Hooks::addAction( 'admin_enqueue_scripts', MilestoneBlock::class, 'enqueue_assets' );
	}
}
