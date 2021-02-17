<?php
namespace Give\Tracking\Contracts;

use Give\Tracking\Enum\EventType;
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
	 * @var EventType
	 */
	protected $eventType;

	/**
	 * @var Track
	 */
	protected $track;

	/**
	 * @var string
	 */
	protected $dataClassName;

	/**
	 * TrackEvent constructor.
	 *
	 * @param  Track  $track
	 */
	public function __construct( Track $track ) {
		$this->track = $track;
	}

	/**
	 * Record track id and data.
	 *
	 * @since 2.10.0
	 */
	public function record() {
		$this->track->recordTrack( $this->eventType, $this->dataClassName );
	}
}
