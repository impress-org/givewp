<?php

namespace Give\Form\LegacyConsumer\Commands;

interface HookCommandInterface {
	public function __invoke( $hook );
}
