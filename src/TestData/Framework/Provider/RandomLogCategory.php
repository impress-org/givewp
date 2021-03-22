<?php

namespace Give\TestData\Framework\Provider;

use Give\Log\ValueObjects\LogCategory;

class RandomLogCategory extends RandomProvider {
	public function __invoke() {
		return $this->faker->randomElement( LogCategory::getAll() );
	}
}
