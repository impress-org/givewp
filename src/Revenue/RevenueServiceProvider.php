<?php
namespace  Give\Revenue;

use Give\Revenue\Repositories\Revenue as RevenueRepository;
use Give\Revenue\Database\Revenue;
use Give\ServiceProviders\ServiceProvider;

/**
 * Class DatabaseTables
 * @package Give\ServiceProviders
 *
 * @since 2.9.0
 */
class RevenueServiceProvider implements ServiceProvider {
	/**
	 * @inheritDoc
	 */
	public function register() {
		give()->singleton( Revenue::class );

		give()->bind(
			RevenueRepository::class,
			function() {
				new RevenueRepository( give( Revenue::class ) );
			}
		);
	}

	/**
	 * @inheritdoc
	 */
	public function boot() {}
}
