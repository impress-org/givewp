<?php

namespace Give\Log\ValueObjects;

use InvalidArgumentException;
use ReflectionClass;
use ReflectionException;

/**
 * Class ValueObject
 * @package Give\Log\ValueObjects
 *
 * @since 2.9.7
 */
abstract class ValueObject {
	/**
	 * @var mixed
	 */
	protected $value;

	/**
	 * ValueObject constructor.
	 *
	 * @param string $value
	 */
	public function __construct( $value ) {
		if ( ! self::isValid( $value ) ) {
			throw new InvalidArgumentException(
				sprintf( 'Invalid property %s of class %s', $value, static::class )
			);
		}

		$this->value = strtoupper( $value );
	}


	/**
	 * Get an array of defined constants
	 *
	 * @return array
	 */
	public static function getAll() {

		static $constants = [];

		if ( ! isset( $constants[ static::class ] ) ) {
			try {
				$reflection                 = new ReflectionClass( static::class );
				$constants[ static::class ] = $reflection->getConstants();
			} catch ( ReflectionException $exception ) {
				return [];
			}
		}

		return $constants[ static::class ];
	}

	/**
	 * Get value
	 *
	 * @return mixed|null
	 */
	public function getValue() {
		$constants = self::getAll();

		if ( isset( $constants[ $this->value ] ) ) {
			return $constants[ $this->value ];
		}

		return null;
	}

	/**
	 * Get default value
	 *
	 * @return string
	 */
	abstract public static function getDefault();

	/**
	 * Check if value is valid
	 *
	 * @param string $value
	 * @return bool
	 */
	public static function isValid( $value ) {
		return array_key_exists(
			strtoupper( $value ),
			self::getAll()
		);
	}

	/**
	 * @param  string  $name
	 * @param  array  $args
	 *
	 * @return static
	 */
	public static function __callStatic( $name, $args ) {
		return new static( $name );
	}
}
