<?php
namespace Give\Tracking\TrackingData;

use Give\Framework\Collection;

/**
 * Class PluginData
 *
 * Represents the plugin data.
 *
 * @since 2.10.0
 * @package Give\Tracking\TrackingData
 */
class PluginData implements Collection {

	/**
	 * Returns the collection data.
	 *
	 * @return array The collection data.
	 */
	public function get() {
		return [
			'plugins' => $this->getPluginData(),
		];
	}

	/**
	 * Returns all plugins.
	 *
	 * @return array The formatted plugins.
	 */
	protected function getPluginData() {

		if ( ! function_exists( 'get_plugin_data' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$plugins = wp_get_active_and_valid_plugins();
		$plugins = array_map( 'get_plugin_data', $plugins );
		$plugins = array_map( [ $this, 'formatPlugin' ], $plugins );

		$plugin_data = [];
		foreach ( $plugins as $plugin ) {
			$plugin_key                 = sanitize_title( $plugin['name'] );
			$plugin_data[ $plugin_key ] = $plugin;
		}

		return $plugin_data;
	}

	/**
	 * Formats the plugin array.
	 *
	 * @param  array  $plugin  The plugin details.
	 *
	 * @return array The formatted array.
	 */
	protected function formatPlugin( array $plugin ) {
		return [
			'name'    => $plugin['Name'],
			'version' => $plugin['Version'],
		];
	}
}

