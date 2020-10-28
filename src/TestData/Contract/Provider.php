<?php

namespace Give\TestData\Contract;

/**
 * Providers represent specific data types that may be generated based on specific requirements
 * and are application specific, as opposed to generic data types, like names, emails, etc.
 * Examples include binary state or attributes restricted to a preset list of options.
 */
interface Provider {

	/**
	 * Providers use the invoke magic method so as to be callable as a function.
	 */
	public function __invoke();
}
