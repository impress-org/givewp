<?php

namespace Give\Log;

use Give\Log\ValueObjects\LogType;
use Give\Log\ValueObjects\LogCategory;

/**
 * Class LogFactory
 * @package Give\Log
 *
 * @method error( string $message, string $source, array $context = [] )
 * @method warning( string $message, string $source, array $context = [] )
 * @method notice( string $message, string $source, array $context = [] )
 * @method success( string $message, string $source, array $context = [] )
 * @method info( string $message, string $source, array $context = [] )
 * @method http( string $message, string $source, array $context = [] )
 */
class LogFactory {
	/**
	 * @var string
	 */
	private $type;

	/**
	 * @var array
	 */
	private $context;

	/**
	 * LogModel constructor.
	 *
	 * @param  string  $type
	 * @param  array  $context
	 */
	private function __construct( $type, $context ) {
		$this->type    = $this->getType( $type );
		$this->context = $this->getContext( $context );
	}

	/**
	 * Make log record instance
	 *
	 * @param  string  $type
	 * @param  array  $context
	 *
	 * @return LogFactory
	 */
	public static function make( $type, $context ) {
		return new self( $type, $context );
	}

	/**
	 * @param string $type
	 * @param array $args
	 */
	public function __call( $type, $args ) {
		list ( $message, $source, $additionalContext ) = array_pad( $args, 3, null );

		$data = [
			'message' => $message,
			'source'  => $source,
		];

		// Update existing context
		$context = array_merge( $this->context, $data );

		// Add additional context
		if ( is_array( $additionalContext ) ) {
			$context['context'] = array_merge( $context['context'], $additionalContext );
		}
		// Set the type and context again
		$this->type    = $this->getType( $type );
		$this->context = $this->getContext( $context );

		$this->save();
	}

	/**
	 * Get the log type
	 * If the log type is not supported, it fallbacks to notice type
	 *
	 * @param  string  $type
	 *
	 * @return string
	 */
	private function getType( $type ) {
		if ( ! array_key_exists( $type, LogType::getAllTypes() ) ) {
			return LogType::NOTICE;
		}

		return $type;
	}

	/**
	 * Get the the log context
	 *
	 * @param  array  $context
	 *
	 * @return array
	 */
	private function getContext( $context ) {
		$defaults = [
			'migration_id' => null,
			'category'     => LogCategory::CORE,
			'source'       => esc_html__( 'Give Core', 'give' ),
			'message'      => esc_html__( 'Something went wrong', 'give' ),
		];

		$context = array_merge( $defaults, $context );

		// Get default options from context
		$data = array_filter(
			$context,
			function( $key ) use ( $defaults ) {
				return array_key_exists( $key, $defaults );
			},
			ARRAY_FILTER_USE_KEY
		);

		// Get additional context
		$data['context'] = array_diff(
			isset( $context['context'] ) ? $context['context'] : $context,
			$data
		);

		return $data;
	}

	/**
	 * Save log record
	 */
	public function save() {
		$logRepository = give( LogRepository::class );
		$logRepository->insertLog(
			$this->type,
			$this->context['message'],
			$this->context['category'],
			$this->context['source'],
			$this->context['migration_id'],
			$this->context['context']
		);
	}
}
