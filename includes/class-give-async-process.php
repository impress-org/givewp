<?php
use Give\Framework\WordPressLibraries\WPAsyncRequest;

/**
 * Background Process
 *
 * Uses https://github.com/A5hleyRich/wp-background-processing to handle DB
 * updates in the background.
 *
 * @class    Give_Async_Request
 * @since 3.1.2 replace WP_Async_Request with namespaced version WPAsyncRequest.
 * @version  2.0.0
 * @package  Give/Classes
 * @category Class
 * @author   GiveWP
 *
 * @since 2.32.0 updated to extend WPAsyncRequest
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Give_Background_Updater Class.
 */
class Give_Async_Process extends WPAsyncRequest {
	/**
	 * Prefix
	 *
	 * @var string
	 * @access protected
	 */
	protected $prefix = 'give';

	/**
	 * Dispatch updater.
	 *
	 * Updater will still run via cron job if this fails for any reason.
	 */
	public function dispatch() {
		/* @var WP_Async_Request $dispatched */
		parent::dispatch();
	}

	/**
	 * Handle
	 *
	 * Override this method to perform any actions required
	 * during the async request.
	 */
	protected function handle() {
		/*
		 * $data = array(
		 *  'hook'     => '', // required
		 *  'data' => {mixed} // required
		 * )
		 */

		$_post = give_clean( $_POST );

		if ( empty( $_post ) || empty( $_post['data'] ) || empty( $_post['hook'] ) ) {
			exit();
		}

		/**
		 * Fire the hook.
		 */
		do_action( $_post['hook'], $_post['data'] );

		exit();
	}
}
