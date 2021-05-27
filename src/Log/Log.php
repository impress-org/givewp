<?php

namespace Give\Log;

use Exception;

/**
 * Class Log
 *
 * The static facade intended to be the primary way of logging within GiveWP to make life easier.
 *
 * @package Give\Log
 * @since 2.10.0
 *
 * @note There are two special keywords used in the context that are representing category and source.
 * The default value for the Category is "Core" and for the source is "Give Core"
 * If you want to change the category and/or source, you should provide them as context attributes.
 * Source and category attributes should be written lowercase.
 *
 * @example
 *
 * Log::error( 'Error message', [
 *     'category' => 'Payment',
 *     'source' => 'Stripe add-on'
 * ] );
 *
 * @note Use as many contexts attributes as you need. The more the better.
 *
 * @example
 *
 *  Log::error( 'Error message', [
 *     'category' => 'Payment',
 *     'source' => 'Stripe add-on',
 *     'donation_id' => $donationId,
 *     'donor_id' => $donorId
 * ] );
 *
 * @note You can use an array or object as a context attribute value.
 *
 * @example
 *
 * try {
 *     something();
 * } catch ( Exception $exception ) {
 *   Log::error( 'Something went wrong', [
 *      'exception' => $exception,
 *      'additional_info' => [
 *          'donation_id' => $donationId
 *       ]
 *   ] );
 * }
 *
 *
 * @method static error( string $message, array $context = [] )
 * @method static warning( string $message, array $context = [] )
 * @method static notice( string $message, array $context = [] )
 * @method static success( string $message, array $context = [] )
 * @method static info( string $message, array $context = [] )
 * @method static http( string $message, array $context = [] )
 * @method static spam( string $message, array $context = [] )
 */
class Log {
	public function __call( $name, $arguments ) {
		list ( $message, $context ) = array_pad( $arguments, 2, null );

		if ( is_array( $context ) ) {
			// Convert context values to string
			$context = array_map(
				function ( $item ) {
					if ( is_array( $item ) || is_object( $item ) ) {
						$item = print_r( $item, true );
					}

					return $item;
				},
				$context
			);

			// Default fields
			$data = array_filter(
				$context,
				function ( $key ) {
					return array_key_exists( $key, LogFactory::getDefaults() );
				},
				ARRAY_FILTER_USE_KEY
			);

			// Additional context
			$data['context'] = array_diff(
				$context,
				$data
			);
		}

		// Set message
		if ( ! is_null( $message ) ) {
			$data['message'] = $message;
		}

		// Set type
		$data['type'] = $name;

		try {
			$log = LogFactory::makeFromArray( $data );
			$log->save();

			return $log;
		} catch ( Exception $exception ) {
			error_log( $exception->getMessage() );
		}
	}

	/**
	 * Static helper for calling the logger methods
	 *
	 * @since 2.11.1
	 *
	 * @param string $name
	 * @param array  $arguments
	 */
	public static function __callStatic( $name, $arguments ) {
		/** @var Log $logger */
		$logger = give( __CLASS__ );

		call_user_func_array( [ $logger, $name ], $arguments );
	}
}
