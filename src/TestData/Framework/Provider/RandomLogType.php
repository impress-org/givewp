<?php

namespace Give\TestData\Framework\Provider;

use Give\Log\ValueObjects\LogType;

class RandomLogType extends RandomProvider {
	public function __invoke() {
		return $this->faker->randomElement( LogType::getAll() );
	}
}
