<?php
namespace Give\Tracking\Contracts;

use Give\Tracking\Track;

/**
 * Class TrackEvent
 *
 * This class represents a send tracked data request event
 *
 * @since 2.10.0
 * @package Give\Tracking\Contracts
 */
abstract class TrackEvent {
	/**
	 * @var string
	 */
	protected $trackId = '';

	/**
	 * @var Track
	 */
	protected $track;

	/**
	 * @var TrackData
	 */
	protected $data;

	/**
	 * TrackEvent constructor.
	 *
	 * @param  Track  $track
	 * @param  TrackData  $data
	 */
	public function __construct( Track $track, TrackData $data ) {
		$this->track = $track;
		$this->data  = $data;
	}

	/**
	 * Record track id and data.
	 *
	 * @since 2.10.0
	 */
	public function record() {
		$this->track->recordTrack( $this->trackId, $this->data->get() );
	}
}
