<?php

namespace Give\Form\LegacyConsumer\Commands\Helper;

class ClosureProxy {

	/**
	 * @param callable $callback
	 * @param HookCommandInterface $command
	 */
	public function __construct( callable $callback, $command ) {
		$this->callback = $callback;
		$this->command  = $command;
	}

	public function with( $attributes = [] ) {

		// Merge command closure properties with attributes.
		$reflection = new \ReflectionClass( $this->command );
		foreach ( $reflection->getProperties() as $property ) {
			if ( is_a( $this->command->{$property->name}, ClosureProxy::class ) ) {
				$attributes[ $property->name ] = $this->command->{$property->name};
			}
		}

		// Return closure bound with attributes.
		return \Closure::bind( $this->callback, new AttributeBag( $attributes ), AttributeBag::class );
	}
}
