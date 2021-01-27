<?php

namespace Give\Log;

use InvalidArgumentException;
use Give\Framework\Migrations\Contracts\Migration;

/**
 * Class Log
 * @package Give\Log
 *
 * @since 2.9.7
 *
 * @method static error( string $message, array $context )
 * @method static warning( string $message, array $context )
 * @method static notice( string $message, array $context )
 * @method static success( string $message, array $context )
 * @method static info( string $message, array $context )
 * @method static http( string $message, array $context )
 */
class Log {
	// Log types
	const ERROR   = 'error';
	const WARNING = 'warning';
	const NOTICE  = 'notice';
	const SUCCESS = 'success';
	const INFO    = 'info';
	const HTTP    = 'http';

	// Predefined log categories
	const CORE_CATEGORY      = 'Core'; // default
	const PAYMENT_CATEGORY   = 'Payment';
	const MIGRATION_CATEGORY = 'Migration';

	/**
	 * @param  string  $type
	 * @param  array  $args
	 */
	public static function __callStatic( $type, $args ) {
		$types = static::getLogTypes();

		if ( ! array_key_exists( $type, $types ) ) {
			throw new InvalidArgumentException(
				sprintf( 'Invalid log type %s. Use one of the available types (%s)', $type, implode( ',', array_keys( $types ) ) )
			);
		}

		$params = array_pad( $args, 2, null );

		static::logMessage( $type, $params[0], $params[1] );
	}

	/**
	 * Add payment log
	 *
	 * @param  string  $type  Log type
	 * @param  string  $message  Describe what happened
	 * @param  string  $source  Source of this log e.g. Funds Add-on
	 * @param  array  $context  Log meta data
	 */
	public static function payment( $type, $message, $source, $context = [] ) {
		$context['category'] = self::PAYMENT_CATEGORY;
		$context['source']   = $source;

		static::logMessage( $type, $message, $context );
	}

	/**
	 * Add migration log
	 *
	 * @param  string  $type  Log type
	 * @param  string  $migrationClass  Migration class name
	 * @param  string  $migrationDescription  Migration description, what this migration do
	 * @param  string  $source  Source of this log e.g. Funds Add-on
	 * @param  array  $context  Log meta data
	 */
	public static function migration( $type, $migrationClass, $migrationDescription, $source, $context = [] ) {
		if ( ! is_subclass_of( $migrationClass, Migration::class ) ) {
			throw new InvalidArgumentException(
				sprintf( 'Migration class %s must extend the %s class', $migrationClass, Migration::class )
			);
		}

		$context['category']     = self::MIGRATION_CATEGORY;
		$context['source']       = empty( $source ) ? $migrationClass : $source; // Add migration class as a source if source is empty
		$context['migration_id'] = $migrationClass::id();

		static::logMessage( $type, $migrationDescription, $context );
	}

	/**
	 * Log message
	 *
	 * @param  string  $type
	 * @param  string  $message
	 * @param  array  $context
	 *  $context = [
	 *      'category'  => (string) 'Give Core Error'
	 *      'source'    => (string) 'Give Core'
	 *      'exception' => (Exception) $exception
	 *  ]
	 *  Attributes 'category' and 'source' will be used to set the log category and source fields in give_logs table
	 *  everything else will be stored as log meta data
	 *
	 * @return void
	 */
	public static function logMessage( $type, $message, $context = [] ) {
		$logRepository = give( LogRepository::class );

		// Default category and source
		$defaults = [
			'category' => self::CORE_CATEGORY,
			'source'   => esc_html__( 'Give Core', 'give' ),
		];

		$data = wp_parse_args( $context, $defaults );

		$logId = $logRepository->insertLog(
			$type,
			$message,
			$data['category'],
			$data['source']
		);

		unset( $data['category'], $data['source'] );

		// Insert meta data
		foreach ( $data as $key => $value ) {
			$logRepository->insertLogMeta( $logId, $key, $value );
		}
	}

	/**
	 * Get log types
	 *
	 * @return array
	 */
	public static function getLogTypes() {
		return [
			self::ERROR   => esc_html__( 'Error', 'give' ),
			self::WARNING => esc_html__( 'Warning', 'give' ),
			self::NOTICE  => esc_html__( 'Notice', 'give' ),
			self::SUCCESS => esc_html__( 'Success', 'give' ),
			self::INFO    => esc_html__( 'Info', 'give' ),
			self::HTTP    => 'HTTP',
		];
	}
}
