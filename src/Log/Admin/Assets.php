<?php

namespace Give\Log\Admin;

/**
 * Class Assets
 * @package Give\Log\UserInterface
 *
 * @since 2.9.7
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
	}
}
