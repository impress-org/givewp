<?php

namespace Give\DonorDashboards\Pipeline;

/**
 * @since 2.10.0
 */
class DonorProfilePipeline {

	protected $stages;

	public function __construct() {
		$this->stages = [];
	}

	public function pipe( $stage ) {
		$pipeline       = clone $this;
		$this->stages[] = $stage;
		return $this;
	}

	public function process( $payload ) {
		foreach ( $this->stages as $stage ) {
			$payload = $stage( $payload );
		}

		return $payload;
	}

	public function __invoke( $payload ) {
		return $this->process( $payload );
	}
}
