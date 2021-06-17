<?php

namespace Give\Framework\FieldsAPI\Fields\Concerns;

trait CreatesSelfWithName {

	/**
	 * Select constructor
	 *
	 * @param string $name
	 */
	protected function __construct( $name ) {
		$this->name = $name;
	}

	/**
	 * @param string $name
	 *
	 * @return static
	 */
	public static function make( $name ) {
		return new static( $name );
	}
}
