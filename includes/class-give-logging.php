<?php

use Give\Log\Log;
use Give\Log\LogFactory;
use Give\Log\LogRepository;
use Give\Log\ValueObjects\LogType;
use Give\Log\Helpers\LogTypeHelper;
/**
 * Class for logging events and errors
 *
 * @package     Give
 * @subpackage  Classes/Give_Logging
 * @copyright   Copyright (c) 2016, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Give_Logging Class
 *
 * A general use class for logging events and errors.
 *
 * @deprecated 2.10.0
 * @use Log
 * @see Log
 *
 * @since 1.0
 */
class Give_Logging {
	/**
	 * @var LogRepository
	 */
	private $logRepository;

	/**
	 * @var LogTypeHelper
	 */
	private $logTypeHelper;

	/**
	 * Class Constructor
	 *
	 * Set up the Give Logging Class.
	 *
	 * @since  1.0
	 * @access public
	 */
	public function __construct() {
		$this->logRepository = give( LogRepository::class );
		$this->logTypeHelper = give( LogTypeHelper::class );
	}

	/**
	 * Create new log entry
	 *
	 * This is just a simple and fast way to log something. Use $this->insert_log()
	 * if you need to store custom meta data.
	 *
	 * @deprecated 2.10.0
	 * @use Log::LOG_TYPE( $message );
	 * @see Log
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param  string $title   Log entry title. Default is empty.
	 * @param  string $message Log entry message. Default is empty.
	 * @param  int    $parent  Log entry parent. Default is 0.
	 * @param  string $type    Log type. Default is empty string.
	 *
	 * @return int             Log ID.
	 */
	public function add( $title = '', $message = '', $parent = 0, $type = '' ) {
		$log_data = [
			'post_title'   => $title,
			'post_content' => $message,
			'post_parent'  => $parent,
			'log_type'     => $type,
		];

		return $this->insert_log( $log_data );
	}

	/**
	 * Helper method used to map the fields from the old system to the new system
	 *
	 * @since 2.10.0
	 *
	 * @param array $logData
	 * @param array $logMeta
	 *
	 * @return array
	 */
	private function getLogData( $logData, $logMeta ) {
		$oldType = isset( $logData['log_type'] )
			? $logData['log_type']
			: LogType::getDefault();

		$data = $this->logTypeHelper->getDataFromType( $oldType );

		$content = esc_html__( 'Something went wrong', 'give' );

		if ( isset( $logData['log_content'] ) ) {
			$content = $logData['log_content'];
		} else {
			if ( isset( $logData['post_title'] ) ) {
				$content = $logData['post_title'];
			}
		}

		return [
			'type'     => $data['type'],
			'category' => $data['category'],
			'message'  => $content,
			'context'  => array_merge( $logData, $logMeta ),
		];
	}

	/**
	 * Get Logs
	 *
	 * Retrieves log items for a particular object ID.
	 *
	 * @deprecated 2.10.0
	 * @use LogRepository::getLogs();
	 * @see LogRepository
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param  int    $object_id Log object ID. Default is 0.
	 * @param  string $type      Log type. Default is empty string.
	 * @param  int    $paged     Page number Default is null.
	 *
	 * @return array             An array of the connected logs.
	 */
	public function get_logs( $object_id = 0, $type = '', $paged = null ) {
		if ( $object_id ) {
			$log = $this->logRepository->getLog( $object_id );

			return [
				(object) [
					'ID'           => $log->getId(),
					'log_date'     => $log->getDate(),
					'log_date_gmt' => '',
					'log_content'  => $log->getMessage(),
					'log_title'    => $log->getCategory() . ' - ' . $log->getSource(),
					'log_type'     => $log->getType(),
				],
			];
		}

		if ( ! empty( $type ) ) {
			$data = [];
			$logs = $this->logRepository->getLogsByType( $type );

			foreach ( $logs as $log ) {
				$data[] = (object) [
					'ID'           => $log->getId(),
					'log_date'     => $log->getDate(),
					'log_date_gmt' => '',
					'log_content'  => $log->getMessage(),
					'log_title'    => $log->getCategory() . ' - ' . $log->getSource(),
					'log_type'     => $log->getType(),
				];
			}

			return $data;
		}

		return [];
	}

	/**
	 * Stores a log entry
	 *
	 * @deprecated 2.10.0
	 * @use Log::LOG_TYPE( $message );
	 * @see Log
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param  array $log_data Log entry data.
	 * @param  array $log_meta Log entry meta.
	 *
	 * @return int             The ID of the newly created log item.
	 */
	public function insert_log( $log_data = [], $log_meta = [] ) {
		// Extract data from parameters
		$data = $this->getLogData( $log_data, $log_meta );

		$backtrace = debug_backtrace();

		// Add more context
		if (
			isset( $backtrace[1] ) &&
			! array_diff( [ 'file', 'line', 'function', 'class' ], array_keys( $backtrace[1] ) )
		) {
			$data['context']['file']     = $backtrace[1]['file'];
			$data['context']['line']     = $backtrace[1]['line'];
			$data['context']['function'] = $backtrace[1]['function'];
			$data['context']['class']    = $backtrace[1]['class'];
		}

		try {
			$log = LogFactory::makeFromArray( $data );
			$log->save();

			return $log->getId();
		} catch ( Exception $exception ) {
			error_log( $exception->getMessage() );
		}
	}

	/**
	 * Update and existing log item
	 *
	 * @deprecated 2.10.0
	 * @use Log::LOG_TYPE( $message );
	 * @see Log
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param  array $log_data Log entry data.
	 * @param  array $log_meta Log entry meta.
	 *
	 * @return bool|null       True if successful, false otherwise.
	 */
	public function update_log( $log_data = [], $log_meta = [] ) {
		return $this->insert_log( $log_data, $log_meta );
	}

	/**
	 * Retrieve all connected logs
	 *
	 * Used for retrieving logs related to particular items, such as a specific donation.
	 * For new table params check: Give_DB_Logs::get_column_defaults and Give_DB_Logs::get_sql#L262
	 *
	 * @deprecated 2.10.0
	 *
	 * @since  1.0
	 * @since  2.0 Added new table logic.
	 * @access public
	 *
	 * @param  array $args Query arguments.
	 *
	 * @return array|false Array if logs were found, false otherwise.
	 */
	public function get_connected_logs( $args = [] ) {
		return false;
	}

	/**
	 * Retrieve Log Count
	 *
	 * Retrieves number of log entries connected to particular object ID.
	 *
	 * @deprecated 2.10.0
	 * @use LogRepository::getTotalCount()
	 * @see LogRepository
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param  int    $object_id  Log object ID. Default is 0.
	 * @param  string $type       Log type. Default is empty string.
	 * @param  array  $meta_query Log meta query. Default is null.
	 * @param  array  $date_query Log data query. Default is null.
	 *
	 * @return int                Log count.
	 */
	public function get_log_count( $object_id = 0, $type = '', $meta_query = null, $date_query = null ) {
		if ( $object_id ) {
			return 0;
		}

		if ( ! empty( $type ) ) {
			$logs = $this->logRepository->getLogsByType( $type );
			return count( $logs );
		}

		return $this->logRepository->getTotalCount();
	}

	/**
	 * Delete Logs
	 *
	 * Remove log entries connected to particular object ID.
	 *
	 * @deprecated 2.10.0
	 * @use LogRepository::deleteLogs()
	 * @see LogRepository
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param  int    $object_id  Log object ID. Default is 0.
	 * @param  string $type       Log type. Default is empty string.
	 * @param  array  $meta_query Log meta query. Default is null.
	 *
	 * @return void
	 */
	public function delete_logs( $object_id = 0, $type = '', $meta_query = null ) {
		$this->logRepository->deleteLogs();
	}

}
