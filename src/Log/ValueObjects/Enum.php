<?php

namespace Give\Log\ValueObjects;

use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use ReflectionClass;
use ReflectionException;

/**
 * Class ValueObject
 * @package Give\Log\ValueObjects
 *
 * @since 2.10.0
 */
abstract class Enum implements EnumInterface {
	/**
	 * @var mixed
	 */
	protected $value;

	/**
	 * ValueObject constructor.
	 *
	 * @param mixed $value
	 */
	public function __construct( $value ) {
		if ( $value instanceof static ) {
			$value = $value->getValue();
		}

		if ( ! self::isValid( $value ) ) {
			throw new InvalidArgumentException(
				sprintf( 'Invalid %s enumeration value provided %s', static::class, $value )
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
	 * @inheritDoc
	 */
	public function getValue() {
		$constants = self::getAll();

		if ( isset( $constants[ $this->value ] ) ) {
			return $constants[ $this->value ];
		}

		return null;
	}

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
	 * @inheritDoc
	 */
	public function equalsTo( $value ) {
		return $value instanceof self && $this->getValue() === $value->getValue();
	}

	/**
	 * @param  string  $name
	 * @param  array  $args
	 *
	 * @return static
	 */
	public static function __callStatic( $name, $args ) {
		if ( self::isValid( $name ) ) {
			return new static( $name );
		}

		throw new InvalidArgumentException( "Invalid argument, does not match constant {$name}" );
	}
}
