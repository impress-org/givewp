<?php

namespace Give\Form\LegacyConsumer\Commands;

/*
 * @unreleased
 */

class CommandFactory implements HookCommandInterface {

	/**
	 * @unreleased
	 *
	 * @var string
	 */
	protected $commandClass;

	/**
	 * @unreleased
	 *
	 * @param string $commandClass
	 */
	public function __construct( $commandClass ) {
		$this->commandClass = $commandClass;
	}

	/**
	 * @unreleased
	 *
	 * @param string $hook
	 *
	 * @return void
	 */
	public function __invoke( $hook ) {
		$command = new $this->commandClass( $hook );
		$command();
	}
}
