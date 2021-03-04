<?php

namespace Give\TestData\Factories;

use Give\TestData\Framework\Factory;

/**
 * Class LogFactory
 * @package Give\TestData\Factories
 */
class LogFactory extends Factory {
	/**
	 * @var string
	 */
	private $type;

	/**
	 * @param string $type
	 */
	public function setLogType( $type ) {
		$this->type = $type;
	}

	/**
	 * @return string
	 */
	public function getLogType() {
		if ( 'random' === $this->type ) {
			return $this->randomLogType();
		}

		return $this->type;
	}
	/**
	 * Donor definition
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function definition() {
		return [
			'type'     => $this->getLogType(),
			'message'  => $this->faker->sentence( $nbWords = 6 ),
			'category' => $this->randomLogCategory(),
			'source'   => $this->faker->sentence( $nbWords = 3 ),
			'context'  => [
				'Info' => $this->faker->sentence( $nbWords = 6 ),
			],
		];
	}
}
