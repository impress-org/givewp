<?php
namespace Give\Tracking\Contracts;

/**
 * Class TrackData
 *
 * This interface represents a Track Data collection
 *
 * @since 2.10.0
 * @package Give\Tracking
 */
interface TrackData {

	/**
	 * Returns the collection data.
	 *
	 * @since 2.10.0
	 * @return array The collection data.
	 */
	public function get();
}
