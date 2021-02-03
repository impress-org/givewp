<?php

namespace Give\MigrationLog;

/**
 * Class MigrationLogModel
 * @package Give\MigrationLog
 *
 * @since 2.9.7
 */
class MigrationLogModel {
	/**
	 * @var string
	 */
	private $id;

	/**
	 * @var string
	 */
	private $status;

	/**
	 * @var string|null
	 */
	private $last_run;

	/**
	 * MigrationModel constructor.
	 *
	 * @param string $id
	 * @param string $status
	 * @param string|null $lastRun
	 */
	public function __construct( $id, $status = '', $lastRun = null ) {
		$this->id       = $id;
		$this->last_run = $lastRun;
		$this->setStatus( $status );
	}

	/**
	 * Set migration status
	 *
	 * @param string $status
	 * @uses MigrationLogStatus
	 * @see MigrationLogStatus::STATUS_NAME
	 *
	 * @return MigrationLogModel
	 */
	public function setStatus( $status ) {
		$this->status = array_key_exists( $status, MigrationLogStatus::getAll() )
			? $status
			: MigrationLogStatus::getDefault();

		return $this;
	}

	/**
	 * @return int
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @return string
	 */
	public function getStatus() {
		return $this->status;
	}

	/**
	 * @return string
	 */
	public function getLastRunDate() {
		return $this->last_run;
	}

	/**
	 * Save migration
	 */
	public function save() {
		give( MigrationLogRepository::class )->save( $this );
	}
}
