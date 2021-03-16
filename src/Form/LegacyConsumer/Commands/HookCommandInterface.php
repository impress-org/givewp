<?php

namespace Give\Form\LegacyConsumer\Commands;

/**
 * @unreleased
 */
interface HookCommandInterface {
	/**
	 * @unreleased
	 *
	 * @param string $hook
	 */
	public function __invoke( $hook );
}
