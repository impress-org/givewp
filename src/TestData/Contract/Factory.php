<?php

namespace Give\TestData\Contract;

/**
 * Factories create
 */
interface Factory {

	/**
	 * Factories should be able to generate a given quantity of items.
	 */
	public function make( $count );

	/**
	 * Data should be generated in a defined shape.
	 */
	public function definition();
}
