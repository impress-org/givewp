<?php

namespace Give\Session;

/**
 * Class Session
 *
 * @package Give\Session
 */
abstract class SessionAccess {
	/**
	 * Session Id.
	 *
	 * @var string
	 */
	private $id;

	/**
	 * Session data.
	 *
	 * @var mixed
	 */
	private $data = [];

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

		$this->data = Give()->session->get( $this->id, $this->data );
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
		if ( ! $this->data ) {
			$this->data = $this->get();
		}

		if ( ! $this->data || array_key_exists( $key, $this->data ) ) {
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
		$session = $this->get();

		if ( ! empty( $session[ $key ] ) ) {
			// Merge data.
			$session[ $key ] = array_merge(
				$session[ $key ],
				$data
			);

		} else {
			$session[ $key ] = $data;
		}

		$this->data = $session;

		return $this->set();
	}


	/**
	 * Store data into session.
	 *
	 * @return string
	 * @since 2.7.0
	 */
	protected function set() {
		return Give()->session->set( $this->id, $this->data );
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
