<?php
namespace Give\ValueObjects;

interface ValueObjects {

	/**
	 * Take array and return object.
	 *
	 * @param array $array
	 *
	 * @return mixed
	 */
	public static function fromArray( $array );
}
