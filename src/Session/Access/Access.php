<?php

namespace Give\Session\Access;

/**
 * Class Access
 *
 * In legacy core session data load in array which contains multiple keys like give_purchase, receipt_access etc.
 * This class helps to convert them into objects. Every subclass will treat a specific key as group of session data.
 *
 * @package Give\Session
 */
abstract class Access {
	/**
	 * Session Id.
	 *
	 * @var string
	 */
	protected $sessionKey;

	/**
	 * Session data.
	 *
	 * @var mixed
	 */
	protected $data;

	/**
	 *
	 * Class constructor.
	 */
	public function __construct() {
		$this->data = $this->get();
	}

	/**
	 * Get data from session.
	 *
	 * @return array|bool|string
	 * @since 2.7.0
	 */
	public function get() {
		if ( $this->data ) {
			return $this->data;
		}

		$this->data = Give()->session->get( $this->sessionKey, $this->data );
		return $this->data;
	}

	/**
	 * Get data from session.
	 *
	 * @param string $key
	 * @return array|null
	 * @since 2.7.0
	 */
	public function getByKey( $key ) {
		if ( array_key_exists( $key, $this->data ) ) {
			return null;
		}

		return ! empty( $this->data[ $key ] ) ? $this->data[ $key ] : null;
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
