<?php

namespace Give\MigrationLog;

use InvalidArgumentException;
use Give\Framework\Migrations\Contracts\Migration;

/**
 * Class MigrationLogFactory
 * @package Give\MigrationLog
 *
 * @since 2.9.7
 */
class MigrationLogFactory {

	public static function make( $id, $status = '', $lastRun = null ) {
		return new MigrationLogModel( $id, $status, $lastRun );
	}

	/**
	 * Make MigrationModel instance from Migration class
	 *
	 * @param string $migrationClass
	 *
	 * @return MigrationLogModel
	 */
	public static function makeFromClass( $migrationClass ) {
		if ( ! is_subclass_of( $migrationClass, Migration::class ) ) {
			throw new InvalidArgumentException(
				sprintf( 'Migration class %s must extend the %s class', $migrationClass, Migration::class )
			);
		}
		return new MigrationLogModel( $migrationClass::id() );
	}
}
