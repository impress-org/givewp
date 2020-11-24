<?php
namespace Give\Tracking;

use Give\Tracking\Contracts\Collection;
use InvalidArgumentException;
use JsonSerializable;

/**
 * Class Collector
 *
 * Collects the data from the added collection objects.
 *
 * @since 2.10.0
 * @package Give\Tracking
 */
class Collector implements JsonSerializable {

	/**
	 * Holds the collections.
	 *
	 * @var Collection[]
	 */
	protected $collections = [];

	/**
	 * Adds a collection object to the collections.
	 *
	 * @since 2.10.0
	 * @param  string $collection  The collection class name to add.
	 */
	public function addCollection( $collection ) {
		if ( ! is_subclass_of( $collection, Collection::class ) ) {
			throw new InvalidArgumentException( "{$collection} class must implement the Collection interface" );
		}

		$this->collections[] = $collection;
	}

	/**
	 * Collects the data from the collection objects.
	 *
	 * @since 2.10.0
	 * @return array The collected data.
	 */
	public function collect() {
		$data = [];

		foreach ( $this->collections as $collection ) {
			/* @var Collection $collection */
			$collection = give( $collection );
			$data       = array_merge( $data, $collection->get() );
		}

		return $data;
	}

	/**
	 * @inheritdoc
	 * @return false|mixed|string
	 */
	public function jsonSerialize() {
		return wp_json_encode( $this->collect() );
	}
}

