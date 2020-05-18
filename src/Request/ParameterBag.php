<?php

namespace Give\Request;

/**
 * Class ParameterBag
 *
 * This class is a container for key/value pairs.
 *
 * @package Give\Request
 * @since 2.7.0
 */
class ParameterBag {
	/**
	 * Parameter storage.
	 *
	 * @var array
	 * @since 2.7.0
	 */
	protected $parameters;

	/**
	 * ParameterBag constructor.
	 *
	 * @param  array $parameters
	 * @since 2.7.0
	 */
	public function __construct( $parameters = [] ) {
		$this->parameters = $parameters;
	}

	/**
	 * Returns the parameters.
	 *
	 * @return array An array of parameters
	 * @since 2.7.0
	 */
	public function all() {
		return $this->parameters;
	}

	/**
	 * Returns the parameter keys.
	 *
	 * @return array An array of parameter keys
	 * @since 2.7.0
	 */
	public function keys() {
		return array_keys( $this->parameters );
	}

	/**
	 * Replaces the current parameters by a new set.
	 *
	 * @param  array $parameters
	 * @since 2.7.0
	 */
	public function replace( $parameters = [] ) {
		$this->parameters = $parameters;
	}

	/**
	 * Adds parameters.
	 *
	 * @param  array $parameters
	 * @since 2.7.0
	 */
	public function add( $parameters = [] ) {
		$this->parameters = array_replace( $this->parameters, $parameters );
	}

	/**
	 * Returns a parameter by name.
	 *
	 * @param  string $key
	 * @param  mixed  $default  The default value if the parameter key does not exist
	 *
	 * @return mixed
	 * @since 2.7.0
	 */
	public function get( $key, $default = null ) {
		return $this->has( $key ) ? $this->parameters[ $key ] : $default;
	}

	/**
	 * Returns true if the parameter is defined.
	 *
	 * @param  string $key
	 *
	 * @return bool true if the parameter exists, false otherwise
	 * @since 2.7.0
	 */
	public function has( $key ) {
		return array_key_exists( $key, $this->parameters );
	}

	/**
	 * Returns the parameter value converted to integer.
	 *
	 * @param $key
	 * @param  int $default
	 *
	 * @return int The filtered value
	 * @since 2.7.0
	 */
	public function getInt( $key, $default = 0 ) {
		return (int) $this->get( $key, $default );
	}

	/**
	 * Returns the parameter value converted to boolean.
	 *
	 * @param $key
	 * @param  bool $default
	 *
	 * @return bool The filtered value
	 * @since 2.7.0
	 */
	public function getBoolean( $key, $default = false ) {
		return $this->filter( $key, $default, FILTER_VALIDATE_BOOLEAN );
	}

	/**
	 * Filter key.
	 *
	 * @param  string $key
	 * @param  mixed  $default  Default = null
	 * @param  int    $filter  FILTER_* constant
	 * @param  mixed  $options  Filter options
	 *
	 * @return mixed
	 * @see https://php.net/filter-var
	 * @since 2.7.0
	 */
	public function filter( $key, $default = null, $filter = FILTER_DEFAULT, $options = [] ) {
		$value = $this->get( $key, $default );

		// Always turn $options into an array - this allows filter_var option shortcuts.
		if ( ! is_array( $options ) && $options ) {
			$options = [ 'flags' => $options ];
		}

		// Add a convenience check for arrays.
		if ( is_array( $value ) && ! isset( $options['flags'] ) ) {
			$options['flags'] = FILTER_REQUIRE_ARRAY;
		}

		return filter_var( $value, $filter, $options );
	}

	/**
	 * Returns the number of parameters.
	 *
	 * @return int The number of parameters
	 * @since 2.7.0
	 */
	public function count() {
		return count( $this->parameters );
	}
}
