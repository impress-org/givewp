<?php

namespace Give\DonorProfiles\Pipeline;

class DonorProfilePipeline {

	protected $stages;

	public function __construct() {
		$this->stages = [];
	}

	public function pipe( callable $stage ) {
		$pipeline       = clone $this;
		$this->stages[] = $stage;
		return $pipeline;
	}

	public function process( $payload, callable ...$stages ) {
		foreach ( $stages as $stage ) {
			$payload = $stage( $payload );
		}

		return $payload;
	}

	public function __invoke( $payload ) {
		return $this->process( $payload );
	}
}
