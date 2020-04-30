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
 * @property-read string $sessionKey
 * @property-read array $data
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
		$this->data    = $this->get();
		$this->dataObj = $this->convertToObject( $this->data );
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
	 * @since 2.7.0
	 * @param mixed $data
	 *
	 * @return string
	 */
	public function replace( $data ) {
		$this->data = $data;

		return $this->set();
	}

	/**
	 * Delete session data.
	 *
	 * @since 2.7.0
	 * @param string $key
	 *
	 * @return string
	 */
	public function delete( $key ) {
		if ( array_key_exists( $key, $this->data ) ) {
			unset( $this->data[ $key ] );
		}

		return $this->set();
	}
}
