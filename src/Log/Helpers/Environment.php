<?php

namespace Give\Log\Helpers;

/**
 * Helper class responsible for checking the environment.
 * @package Give\Log\Helpers
 *
 * @since 2.10.0
 */
class Environment {
	/**
	 * Check if current page is logs page.
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	public static function isLogsPage() {
		if ( ! isset( $_GET['page'], $_GET['tab'] ) ) {
			return false;
		}

		if ( 'give-tools' === $_GET['page'] && 'logs' === $_GET['tab'] ) {
			return true;
		}

		return false;
	}
}
