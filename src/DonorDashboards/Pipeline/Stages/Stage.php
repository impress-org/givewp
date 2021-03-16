<?php

namespace Give\DonorDashboards\Pipeline\Stages;

interface Stage {

	/**
	 * Pipeline stages must define an __invoke method, which accepts and returns $payload
	 *
	 * @param mixed $payload
	 * @return mixed
	 *
	 * @since 2.10.0
	 */
	public function __invoke( $payload);

}
