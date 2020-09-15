<?php
namespace  Give\ServiceProviders;

use Give\Revenue\Repositories\Revenue as RevenueRepository;
use Give\Revenue\Database\Revenue;

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
