<?php

namespace Give\MigrationLog;

use InvalidArgumentException;
use Give\Framework\Migrations\Contracts\Migration;

/**
 * Class MigrationLogFactory
 * @package Give\MigrationLog
 *
 * @since 2.10.0
 */
class MigrationLogFactory {
	/**
	 * Make MigrationModel instance
	 *
	 * @param  string  $id
	 * @param  string  $status
	 * @param  mixed|null  $error
	 * @param  string|null  $lastRun
	 *
	 * @return MigrationLogModel
	 */
	public function make( $id, $status = '', $error = null, $lastRun = null ) {
		return new MigrationLogModel( $id, $status, $error, $lastRun );
	}
}
