<?php

namespace Give\Form\LegacyConsumer\Commands;

/**
 * @since 2.10.2
 */
interface HookCommandInterface {
	/**
	 * @since 2.10.2
	 *
	 * @param string $hook
	 */
	public function __invoke( $hook );
}
