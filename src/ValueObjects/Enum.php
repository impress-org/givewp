<?php

namespace Give\ValueObjects;

use InvalidArgumentException;
use ReflectionClass;
use ReflectionException;

/**
 * Class Enum.
 *
 * This abstract class is intended to be used for enumerated value objects. Constant values should either be strings or
 * numbers.
 *
 * To use simply create a subclass with the enumerated values as constants. No properties or methods are needed.
 */
abstract class Enum implements ValueObject, \JsonSerializable {

	/**
	 * @var mixed
	 */
	protected $value;

	/**
	 * Returns an array of all constants and values in the format:
	 * CONSTANT => VALUE.
	 *
	 * @return array
	 */
	public static function all() {
		static $all = [];

		$class = static::class;

		if ( ! isset( $all[ $class ] ) ) {
			try {
				$reflection = new ReflectionClass( $class );
			} catch ( ReflectionException $exception ) {
				return [];
			}

			$all[ $class ] = $reflection->getConstants();
		}

		return $all[ $class ];
	}

	/**
	 * Checks whether.
	 *
	 * @param $value
	 *
	 * @return bool
	 */
	public static function isValid( $value ) {
		return false !== in_array( $value, self::all(), true );
	}

	/**
	 * Converts camel case to snake case in caps: FooBar -> FOO_BAR.
	 *
	 * @param $string
	 *
	 * @return string
	 */
	private static function toConstantCase( $string ) {
		return ltrim( strtoupper( preg_replace( '/[A-Z]([A-Z](?![a-z]))*/', '_$0', $string ) ), '_' );
	}

	/**
	 * Returns the value of the given constant or null if not defined.
	 *
	 * @param string $key
	 *
	 * @return string|null
	 */
	private static function getConstantValue( $key ) {
		$all = self::all();

		return ! empty( $all[ $key ] ) ? $all[ $key ] : null;
	}

	/**
	 * Adds support for `createFoo` static methods wherein an Enum constant is FOO, or any available constant.
	 *
	 * @param string $name
	 * @param array  $arguments
	 *
	 * @return static
	 */
	public static function __callStatic( $name, $arguments ) {
		if ( false !== preg_match( '/^make[A-Z]/', $name ) ) {
			$constant = self::toConstantCase( substr( $name, 4 ) );

			if ( null !== ( $value = self::getConstantValue( $constant ) ) ) {
				return new static( $value );
			}

			throw new InvalidArgumentException( "Invalid argument, does not match constant: $name" );
		}
	}

	/**
	 * Constructs the Enum while checking to make sure the value is a valid enumeration value.
	 *
	 * @since 0.2.0
	 * @since 0.5.0 Add support for same instances of same class
	 *
	 * @param mixed $value
	 */
	public function __construct( $value ) {
		$class = static::class;

		if ( $value instanceof $class ) {
			$this->value = $value->value;
		} elseif ( false === in_array( $value, self::all(), true ) ) {
			throw new InvalidArgumentException( "Invalid {$class} enumeration value provided: $value" );
		} else {
			$this->value = $value;
		}
	}

	/**
	 * Returns the internal value when invoked as a function.
	 *
	 * @return mixed
	 */
	public function __invoke() {
		return $this->value;
	}

	/**
	 * Adds support for `$enum->isFoo()` methods where FOO is a child constant.
	 *
	 * @param string $name
	 * @param array  $arguments
	 *
	 * @return bool
	 */
	public function __call( $name, $arguments ) {
		if ( false !== preg_match( '/^is[A-Z]/', $name ) ) {
			$constant = self::toConstantCase( substr( $name, 2 ) );

			if ( null !== ( $value = self::getConstantValue( $constant ) ) ) {
				return $this->is( $value );
			}

			throw new InvalidArgumentException( "Invalid argument, does not match constant: $name" );
		}

		throw new InvalidArgumentException( "Invalid argument called: $name" );
	}

	/**
	 * Converts the object into its value in string form.
	 *
	 * @return string
	 */
	public function __toString() {
		return (string) $this->value;
	}

	/**
	 * Compares the value to another Enum or scalar value.
	 *
	 * @param mixed|static $value
	 *
	 * @return bool
	 */
	public function is( $value ) {
		return is_object( $value ) && is_callable( $value )
			? $value instanceof static && $value() === $this->value
			: $value === $this->value;
	}

	/**
	 * Returns this internal value for user in serialization.
	 *
	 * @return mixed
	 */
	public function jsonSerialize() {
		return $this->value;
	}
}
