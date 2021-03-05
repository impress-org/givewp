<?php

namespace Give\Log\Commands;

use Give\Framework\Database\Exceptions\DatabaseQueryException;
use WP_CLI;
use Give\Log\LogRepository;


/**
 * Class FlushLogsCommand
 * @package Give\Log\Commands
 *
 * A WP-CLI command for flushing logs
 */
class FlushLogsCommand {
	/**
	 * @var LogRepository
	 */
	private $logRepository;

	/**
	 * FlushLogsCommand constructor.
	 *
	 * @param  LogRepository  $repository
	 */
	public function __construct( LogRepository $repository ) {
		$this->logRepository = $repository;
	}

	/**
	 * Flush logs

	 * ## EXAMPLE
	 *
	 *     wp give flush-logs
	 */
	public function __invoke() {
		try {
			$this->logRepository->flushLogs();
			WP_CLI::success( 'Logs flushed' );
		} catch ( \Exception $e ) {
			WP_CLI::error( $e->getMessage() );
		}
	}
}

