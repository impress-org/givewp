<?php

namespace Give\Log;

/**
 * Class LogFactory
 * @package Give\Log
 *
 * @since 2.9.7
 */
class LogFactory {
	/**
	 * Make LogModel instance
	 *
	 * @param  string  $type
	 * @param  string  $message
	 * @param  string  $category
	 * @param  string  $source
	 * @param  string|null  $migrationId
	 * @param  array  $context
	 * @param  int|null  $logId
	 *
	 * @return LogModel
	 */
	public static function make( $type, $message, $category, $source, $migrationId = null, $context = [], $logId = null ) {
		return new LogModel( $type, $message, $category, $source, $migrationId, $context, $logId );
	}

	/**
	 * Make LogModel instance from array of data
	 *
	 * @param array $data
	 *
	 * @return LogModel
	 */
	public static function makeFromArray( $data ) {
		// Get default
		$data = array_merge( static::getDefaults(), $data );

		return new LogModel(
			$data['type'],
			$data['message'],
			$data['category'],
			$data['source'],
			$data['migration_id'],
			$data['context'],
			$data['id']
		);
	}

	/**
	 * Get log default fields array
	 *
	 * @return array
	 */
	public static function getDefaults() {
		return [
			'type'         => LogType::NOTICE,
			'message'      => esc_html__( 'Something went wrong', 'give' ),
			'category'     => LogCategory::CORE,
			'source'       => esc_html__( 'Give Core', 'give' ),
			'migration_id' => null,
			'context'      => [],
			'id'           => null,
		];
	}
}
