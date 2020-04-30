<?php

namespace Give\Session\Access;

use stdClass;

/**
 * Class Access
 *
 * In legacy core session data load in array which contains multiple keys like give_purchase, receipt_access etc.
 * This class helps to convert them into objects. Every subclass will treat a specific key as group of session data.
 *
 * @package Give\Session
 *
 * @property-read string   $sessionKey
 * @property-read array    $data
 * @property-read stdClass $dataObj
 */
abstract class Access {
	/**
	 * Session Id.
	 *
	 * @var string
	 */
	protected $sessionKey;

	/**
	 * Session data as array.
	 * We use this array internally to perform database related operation.
	 *
	 * @var mixed
	 */
	protected $data;

	/**
	 * Session data as object.
	 * Session data in object format will be return when query.
	 *
	 * @var stdClass
	 */
	protected $dataObj;

	/**
	 * Class constructor.
	 */
	public function __construct() {
		$this->get();
	}

	/**
	 * Convert session data to object.
	 *
	 * @param array $data
	 *
	 * @return stdClass
	 * @since 2.7.0
	 */
	protected function convertToObject( $data ) {
		$dataObj = new stdClass();
		$data    = $this->renameArrayKeysToPropertyNames( $data );

		foreach ( $data as $key => $value ) {
			if ( is_array( $value ) ) {
				$dataObj->{$key} = $this->convertToObject( $value );
				continue;
			}

			$dataObj->{$key} = $value;
		}

		return $dataObj;
	}

	/**
	 * Get data from session.
	 *
	 * @return stdClass
	 * @since 2.7.0
	 */
	public function get() {
		if ( $this->dataObj ) {
			return $this->dataObj;
		}

		$this->data    = Give()->session->get( $this->sessionKey, $this->data );
		$this->dataObj = $this->convertToObject( $this->data );

		return $this->dataObj;
	}

	/**
	 * Get data from session.
	 *
	 * @param string $key
	 *
	 * @return stdClass
	 * @since 2.7.0
	 */
	public function getByKey( $key ) {
		if ( ! property_exists( $this->dataObj, $key ) ) {
			return null;
		}

		return ! empty( $this->dataObj->{$key} ) ? $this->dataObj->{$key} : null;
	}

	/**
	 * Save/Replace/Remove data into session
	 *
	 * @param string $key
	 * @param mixed  $data
	 *
	 * @return string
	 */
	public function store( $key, $data ) {
		if ( ! empty( $this->data[ $key ] ) ) {
			// Merge data.
			$this->data[ $key ] = array_merge(
				$this->data[ $key ],
				$data
			);

		} else {
			$this->data[ $key ] = $data;
		}

		return $this->set();
	}


	/**
	 * Store data into session.
	 *
	 * @return string
	 * @since 2.7.0
	 */
	protected function set() {
		$this->dataObj = $this->convertToObject( $this->data );

		return Give()->session->set( $this->sessionKey, $this->data );
	}

	/**
	 * Replace session data.
	 *
	 * @param mixed $data
	 *
	 * @return string
	 * @since 2.7.0
	 */
	public function replace( $data ) {
		$this->data = $data;

		return $this->set();
	}

	/**
	 * Delete session data.
	 *
	 * @param string $key
	 *
	 * @return string
	 * @since 2.7.0
	 */
	public function delete( $key ) {
		if ( array_key_exists( $key, $this->data ) ) {
			unset( $this->data[ $key ] );
		}

		return $this->set();
	}

	/**
	 * Return session data in array format.
	 *
	 * @return array
	 * @since 2.7.0
	 */
	public function toArray() {
		return $this->data;
	}

	/**
	 * Rename array key to property name
	 *
	 * @param array $data
	 *
	 * @return array
	 * @since 2.7.0
	 */
	protected function renameArrayKeysToPropertyNames( $data ) {

		foreach ( $data as $key => $value ) {
			// Convert array key string to property name.
			// Remove other then char, dash, give related prefix and hyphen and prefix.
			$newName  = preg_replace( '/[^a-zA-Z0-9_\-]/', '', $key );
			$newName  = str_replace( array( '_give', 'give-', 'give_', 'give' ), '', $newName );
			$keyParts = preg_split( '/(-|_)/', $newName );
			$keyParts = array_map( 'ucfirst', array_filter( $keyParts ) );
			$newName  = lcfirst( implode( '', $keyParts ) );

			// Remove old key/value pair if renamed.
			if ( $key !== $newName ) {
				unset( $data[ $key ] );
			}

			if ( is_array( $value ) ) {
				// Process array.
				$data[ $newName ] = $this->renameArrayKeysToPropertyNames( $value );
				continue;
			}

			$data[ $newName ] = $value;
		}

		return $data;
	}
}
