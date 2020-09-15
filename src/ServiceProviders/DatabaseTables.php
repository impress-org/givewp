<?php
namespace  Give\ServiceProviders;

use Give\Database\Tables\Revenue;

/**
 * Class DatabaseTables
 * @package Give\ServiceProviders
 *
 * @since 2.9.0
 */
class DatabaseTables implements ServiceProvider {
	/**
	 * Table list.
	 * @since 2.9.0
	 * @var string[]
	 */
	private $tables = [
		Revenue::class,
	];

	/**
	 * Table list.
	 * @since 2.9.0
	 * @var string[]
	 */
	private $repositories = [
		Revenue::class => \Give\Database\Repositories\Revenue::class,
	];

	/**
	 * @inheritDoc
	 */
	public function register() {
		foreach ( $this->tables as $table ) {
			give()->singleton( $table );
		}

		foreach ( $this->repositories as $table => $repository ) {
			give()->bind(
				$repository,
				function() use ( $repository, $table ) {
					new $repository( give( $table ) );
				}
			);
		}
	}

	/**
	 * @inheritdoc
	 */
	public function boot() {}
}
