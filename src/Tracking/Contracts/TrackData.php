<?php
namespace Give\Tracking\Contracts;

/**
 * Class Collection
 *
 * Interface that represents a collection.
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
