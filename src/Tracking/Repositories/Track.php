<?php

namespace Give\Tracking\Repositories;

use \Give\Tracking\Track as TrackModel;

/**
 * Class Track
 * @package Give\Tracking\Repositories
 *
 * @since 2.10.0
 */
class Track {
	/**
	 * @var TrackModel
	 */
	private $trackModel;

	/**
	 * Track constructor.
	 *
	 * @param  TrackModel  $trackModel
	 */
	public function __construct( TrackModel $trackModel ) {
		$this->trackModel = $trackModel;
	}
}
