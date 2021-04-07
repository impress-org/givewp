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
	 * @var string
	 */
	private $category;

	/**
	 * @param string $type
	 */
	public function setLogType( $type ) {
		$this->type = $type;
	}

	/**
	 * @param string $category
	 */
	public function setLogCategory( $category ) {
		$this->category = $category;
	}

	/**
	 * @param string $category
	 */
	public function setLogSource( $source ) {
		$this->source = $source;
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
	 * @return string
	 */
	public function getLogCategory() {
		if ( 'random' === $this->category ) {
			return $this->randomLogCategory();
		}

		return $this->category;
	}

	/**
	 * @return string
	 */
	public function getLogSource() {
		if ( 'random' === $this->source ) {
			return $this->faker->sentence( $nbWords = 3 );
		}

		return $this->source;
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
			'category' => $this->getLogCategory(),
			'source'   => $this->getLogSource(),
			'context'  => [
				'Info' => $this->faker->sentence( $nbWords = 6 ),
			],
		];
	}
}
