<?php

namespace Give\TestData;

class DonorFactory extends Framework\Factory {
	public function definition() {
		return [
			'first_name' => $this->faker->firstName(),
			'last_name'  => $this->faker->lastName(),
			'email'      => $this->faker->safeEmail(),
		];
	}
}
