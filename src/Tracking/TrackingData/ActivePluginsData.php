<?php
namespace Give\Tracking\TrackingData;

use Give\Framework\Collection;

/**
 * Class ActivePluginsData
 *
 * Represents the plugin data.
 *
 * @since 2.10.0
 * @package Give\Tracking\TrackingData
 */
class ActivePluginsData implements Collection {

	/**
	 * Returns the collection data.
	 *
	 * @since 2.10.0
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
	 * @since 2.10.0
	 *
	 * @return array The formatted plugins.
	 */
	private function getPluginData() {
		$plugins = give_get_plugins();
		$plugins = array_filter( $plugins, [ $this, 'isPluginActive' ] );
		$plugins = array_map( [ $this, 'formatPlugin' ], $plugins );

		$plugin_data = [];
		foreach ( $plugins as $plugin ) {
			$plugin_key                 = sanitize_title( $plugin['name'] );
			$plugin_data[ $plugin_key ] = $plugin;
		}

		return $plugin_data;
	}

	/**
	 * Returns whether or not plugin active.
	 *
	 * @since 2.10.0
	 *
	 * @param  array  $plugin  The plugin details.
	 *
	 * @return bool
	 */
	private function isPluginActive( array $plugin ) {
		return 'active' === $plugin['Status'];
	}

	/**
	 * Formats the plugin array.
	 *
	 * @since 2.10.0
	 *
	 * @param  array  $plugin  The plugin details.
	 *
	 * @return array The formatted array.
	 */
	private function formatPlugin( array $plugin ) {
		return [
			'name'    => $plugin['Name'],
			'version' => $plugin['Version'],
			'type'    => $plugin['Type'],
		];
	}
}

