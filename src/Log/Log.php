<?php

namespace Give\Log;

use InvalidArgumentException;
use Give\Log\ValueObjects\LogType;
use Give\Log\ValueObjects\LogCategory;
use Give\Framework\Migrations\Contracts\Migration;

/**
 * Class Log
 * @package Give\Log
 *
 * @since 2.9.7
 *
 * @method static error( string $message, array $context = [] )
 * @method static warning( string $message, array $context = [] )
 * @method static notice( string $message, array $context = [] )
 * @method static success( string $message, array $context = [] )
 * @method static info( string $message, array $context = [] )
 * @method static http( string $message, array $context = [] )
 */
class Log {
	/**
	 * @param  string  $type
	 * @param  array  $args
	 */
	public static function __callStatic( $type, $args ) {
		list ( $message, $additionalContext ) = array_pad( $args, 2, null );

		$context = wp_parse_args( $additionalContext, [ 'message' => $message ] );

		LogFactory::make( $type, $context )->save();
	}


	/**
	 * @param string $migrationClass
	 *
	 * @return LogFactory
	 */
	public static function migration( $migrationClass ) {
		if ( ! is_subclass_of( $migrationClass, Migration::class ) ) {
			throw new InvalidArgumentException(
				sprintf( 'Migration class %s must extend the %s class', $migrationClass, Migration::class )
			);
		}

		$context = [
			'category'     => LogCategory::MIGRATION,
			'source'       => $migrationClass,
			'migration_id' => $migrationClass::id(),
		];

		return LogFactory::make( LogType::MIGRATION, $context );
	}
}
