<?php

namespace Give\ServiceProviders;

use Give\Helpers\Hooks;
use Give\Totals\Block as TotalBlock;

class Totals implements ServiceProvider {

	/**
	 * @inheritDoc
	 */
	public function register() {
		give()->singleton( TotalBlock::class );
	}

	/**
	 * @inheritDoc
	 */
	public function boot() {
		Hooks::addAction( 'init', TotalBlock::class, 'addBlock' );
	}
}
