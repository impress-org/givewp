<?php

namespace Give\Log;

use Give\Log\ValueObjects\LogType;

/**
 * Class Assets
 * @package Give\Log\UserInterface
 *
 * @since 2.10.0
 */
class Assets {
	/**
	 * Enqueue scripts
	 */
	public function enqueueScripts() {
		wp_enqueue_script(
			'give-admin-log-list-table-app',
			GIVE_PLUGIN_URL . 'assets/dist/js/give-log-list-table-app.js',
			[ 'wp-element', 'wp-i18n' ],
			GIVE_VERSION,
			true
		);

		wp_localize_script(
			'give-admin-log-list-table-app',
			'GiveLogs',
			[
				'apiRoot'  => esc_url_raw( rest_url( 'give-api/v2/logs' ) ),
				'apiNonce' => wp_create_nonce( 'wp_rest' ),
				'logTypes' => LogType::getTypesTranslated(),
			]
		);
	}
}
