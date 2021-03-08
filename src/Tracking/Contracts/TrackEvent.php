<?php
namespace Give\Tracking\Contracts;

use Give\Tracking\Enum\EventType;
use Give\Tracking\TrackRegisterer;

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
	 * @var TrackRegisterer
	 */
	protected $track;

	/**
	 * @var string
	 */
	protected $dataClassName;

	/**
	 * TrackEvent constructor.
	 *
	 * @param  TrackRegisterer  $track
	 */
	public function __construct( TrackRegisterer $track ) {
		$this->track = $track;
	}

	/**
	 * Record track id and data.
	 *
	 * @since 2.10.0
	 */
	public function record() {
		$this->track->register( $this->eventType, $this->dataClassName );
	}
}
