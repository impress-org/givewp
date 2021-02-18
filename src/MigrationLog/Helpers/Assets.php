<?php

namespace Give\MigrationLog\Helpers;

/**
 * Class Assets
 * @package Give\MigrationLog\Helpers
 *
 * @since 2.10.0
 */
class Assets {
	/**
	 * Enqueue scripts
	 */
	public function enqueueScripts() {
		wp_enqueue_script(
			'give-migrations-list-table-app',
			GIVE_PLUGIN_URL . 'assets/dist/js/give-migrations-list-table-app.js',
			[ 'wp-element', 'wp-i18n' ],
			GIVE_VERSION,
			true
		);

		wp_localize_script(
			'give-migrations-list-table-app',
			'GiveMigrations',
			[
				'apiRoot'  => esc_url_raw( rest_url( 'give-api/v2/migrations' ) ),
				'apiNonce' => wp_create_nonce( 'wp_rest' ),
			]
		);
	}
}
