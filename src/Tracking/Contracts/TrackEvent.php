<?php
namespace Give\Tracking\Contracts;

/**
 * Class TrackEvent
 *
 * @since 2.10.0
 * @package Give\Tracking\Contracts
 */
interface TrackEvent {
	/**
	 * Bootstrap.
	 *
	 * @since 2.10.0
	 */
	public function boot();

	/**
	 * Record track id and data.
	 *
	 * @since 2.10.0
	 */
	public function record();
}
