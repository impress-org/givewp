<?php

namespace Give\Log\Helpers;

use Give\Log\ValueObjects\LogCategory;
use Give\Log\ValueObjects\LogType;

/**
 * Class LogTypeHelper
 * @package Give\Log\Helpers
 *
 * @since 2.10.0
 */
class LogTypeHelper {

	/**
	 * Helper method to get new log type and category based on the old log type value
	 *
	 * @param string $type
	 *
	 * @return array
	 */
	public function getDataFromType( $type ) {
		switch ( $type ) {
			case 'update':
				return [
					'type'     => LogType::ERROR,
					'category' => LogCategory::MIGRATION,
				];

			case 'spam':
				return [
					'type'     => LogType::SPAM,
					'category' => LogCategory::CORE,
				];

			case 'sale':
			case 'stripe':
				return [
					'type'     => LogType::INFO,
					'category' => LogCategory::PAYMENT,
				];

			case 'gateway_error':
				return [
					'type'     => LogType::ERROR,
					'category' => LogCategory::PAYMENT,
				];

			case 'zapier':
				return [
					'type'     => LogType::HTTP,
					'category' => 'Zapier',
				];

			default:
				return [
					'type'     => LogType::ERROR,
					'category' => LogCategory::CORE,
				];
		}
	}
}
