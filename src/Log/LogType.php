<?php

namespace Give\Log;

/**
 * Class LogType
 * @package Give\Log\ValueObjects
 *
 * @since 2.9.7
 */
class LogType {
	const ERROR     = 'error';
	const WARNING   = 'warning';
	const NOTICE    = 'notice';
	const SUCCESS   = 'success';
	const INFO      = 'info';
	const MIGRATION = 'migration';
	const HTTP      = 'http';

	/**
	 * Get all log types
	 *
	 * @return array
	 */
	public static function getAllTypes() {
		return [
			self::ERROR     => esc_html__( 'Error', 'give' ),
			self::WARNING   => esc_html__( 'Warning', 'give' ),
			self::NOTICE    => esc_html__( 'Notice', 'give' ),
			self::SUCCESS   => esc_html__( 'Success', 'give' ),
			self::INFO      => esc_html__( 'Info', 'give' ),
			self::MIGRATION => esc_html__( 'Info', 'give' ),
			self::HTTP      => 'HTTP',
		];
	}
}
