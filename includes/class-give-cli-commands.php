<?php
/**
 * GIVE WP_CLI commands
 *
 * @package give
 * @since 1.7
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }

// Add give command.
WP_CLI::add_command( 'give', 'GIVE_CLI_COMMAND' );


/**
 * Work with Give through WP-CLI
 *
 * Adds CLI support to Give through WP-CLI
 *
 * @since 1.7
 */
class GIVE_CLI_COMMAND {

	/**
	 * This param uses to count process/step inside loop.
	 *
	 * @var int $counter Counter.
	 */
	private static $counter;

	/**
	 * This helps to get information give plugin data.
	 *
	 * @var Give_API Object.
	 */
	private $api;


	/**
	 * GIVE_CLI_Command constructor.
	 */
	public function __construct() {
		$this->api = new Give_API();
	}


	/**
	 * Get Give details
	 *
	 * ## OPTIONS
	 *
	 * None. Returns basic info regarding your Give instance.
	 *
	 * ## EXAMPLES
	 *
	 * wp give details
	 *
	 * @since       1.7
	 * @access		public
	 *
	 * @param		string $args        Command Data.
	 * @param		array  $assoc_args  List of command data.
	 *
	 * @return		void
	 *
	 * @subcommand  details
	 */
	public function details( $args, $assoc_args ) {

		/**
		 * Plugin Information
		 */
		WP_CLI::log( $this->color_message( __( 'Give Version: ', 'give' ) ) . GIVE_VERSION );

		/**
		 * General Information.
		 */
		WP_CLI::log( "\n####   " . $this->color_message( __( 'General information', 'give' ) ) . '   ####' );

		$success_page = give_get_option( 'success_page' );
		$failure_page = give_get_option( 'failure_page' );
		$history_page = give_get_option( 'history_page' );

		WP_CLI::log( $this->color_message( sprintf( __( 'Success Page: ', 'give' ) ) ) . ( $success_page ? "[{$success_page}] " . get_permalink( $success_page ) : __( 'Not Set', 'give' ) ) );
		WP_CLI::log( $this->color_message( __( 'Failed Transaction Page: ', 'give' ) ) . ( $failure_page ? "[{$failure_page}] " . get_permalink( $failure_page ) : __( 'Not Set', 'give' ) ) );
		WP_CLI::log( $this->color_message( __( 'Donation History Page: ', 'give' ) ) . ( $history_page ? "[{$history_page}] " . get_permalink( $history_page ) : __( 'Not Set', 'give' ) ) );
		WP_CLI::log( $this->color_message( __( 'Country: ', 'give' ) ) . give_get_country() );

		/**
		 * Currency Information.
		 */
		$default_gateway = give_get_option( 'default_gateway' );

		WP_CLI::log( "\n####   " . $this->color_message( __( 'Currency Information', 'give' ) ) . '   ####' );

		WP_CLI::log( $this->color_message( __( 'Currency: ', 'give' ), give_get_currency() ) );
		WP_CLI::log( $this->color_message( __( 'Currency Position: ', 'give' ), give_get_currency_position() ) );
		WP_CLI::log( $this->color_message( __( 'Thousand Separator: ', 'give' ), give_get_price_thousand_separator() ) );
		WP_CLI::log( $this->color_message( __( 'Decimal Separator: ', 'give' ), give_get_price_decimal_separator() ) );
		WP_CLI::log( $this->color_message( __( 'Number of Decimals: ', 'give' ), give_get_price_decimals() ) );
		WP_CLI::log( $this->color_message( __( 'Test Mode: ', 'give' ), ( give_get_option( 'test_mode' ) ? __( 'Yes', 'give' ) : __( 'No', 'give' )  ) ) );
		WP_CLI::log( $this->color_message( __( 'Default Gateway: ', 'give' ), ( $default_gateway ? $default_gateway : __( 'Not Set', 'give' )  ) ) );

		// Payment gateways Information.
		$gateways = give_get_ordered_payment_gateways( give_get_payment_gateways() );
		WP_CLI::log( $this->color_message( __( 'Enabled Gateways: ', 'give' ) ) );

		if ( ! empty( $gateways ) ) {
			self::$counter = 1;
			foreach ( $gateways as $gateway ) {
				WP_CLI::log( '    ' . $this->color_message( self::$counter, '. ' ) . $gateway['admin_label'] );
				self::$counter++;
			}
		} else {
			WP_CLI::log( __( 'Not any payment gateways found', 'give' ) );
		}
	}


	/**
	 * Get the forms currently posted on your Give site
	 *
	 * ## OPTIONS
	 *
	 * --id=<form_id>: A specific form ID to retrieve
	 *
	 * ## EXAMPLES
	 *
	 * wp give form --id=103
	 *
	 * @since       1.7
	 * @access		public
	 *
	 * @param		string $args        Command Data.
	 * @param		array  $assoc_args  List of command data.
	 *
	 * @return     void
	 *
	 * @subcommand form
	 */
	public function form( $args, $assoc_args ) {
		$form_id = isset( $assoc_args ) && array_key_exists( 'id', $assoc_args ) ? absint( $assoc_args['id'] ) : false;
		$form = $this->api->get_forms( $form_id );

		// Bailout.
		if ( array_key_exists( 'error', $form ) ) {
			WP_CLI::warning( $form['error'] );
			return;
		}

        if( empty( $form['forms'] ) ) {
            WP_CLI::error( __( 'No form found.', 'give' ) );
            return;
        }


        // Get form.
		$form = current( $form['forms'] );

		// Heading.
		WP_CLI::log( $this->color_message( sprintf( __( 'Report for form id %d', 'give' ), $form_id ) ) );

		foreach ( $form as $key => $info ) {
			switch ( $key ) {
				case 'stats':
					$this->color_main_heading( ucfirst( $key ) );
					foreach ( $info as $heading => $data ) {
						$this->color_sub_heading( ucfirst( $heading ) );
						switch ( $heading ) {
							default:
								foreach ( $data as $subheading => $subdata ) {

									switch ( $subheading ) {
										default:
											WP_CLI::log( $this->color_message( ucfirst( $subheading ) . ': ', $subdata ) );
									}
								}
						}
					}
					break;

				case 'pricing':
				case 'info':
				default:
					$this->color_main_heading( ucfirst( $key ) );
					foreach ( $info as $heading => $data ) {

						switch ( $heading ) {
							case 'id':
								WP_CLI::log( $this->color_message( strtoupper( $heading ) . ': ', $data ) );
								break;

							default:
								$data = empty( $data ) ? __( 'Not set', 'give' ) : $data;
								WP_CLI::log( $this->color_message( ucfirst( $heading ) . ': ', $data ) );
						}
					}
			}
		}
	}


	/**
	 * Get the donors currently on your Give site. Can also be used to create donors records
	 *
	 * ## OPTIONS
	 *
	 * --id=<donor_id>: A specific donor ID to retrieve
	 * --email=<donor_email>: The email address of the donor to retrieve
	 * --create=<number>: The number of arbitrary donors to create. Leave as 1 or blank to create a donor with a specific email
	 *
	 * ## EXAMPLES
	 *
	 * wp give donors --id=103
	 * wp give donors --email=john@test.com
	 * wp give donors --create=1 --email=john@test.com
	 * wp give donors --create=1 --email=john@test.com --name="John Doe"
	 * wp give donors --create=1 --email=john@test.com --name="John Doe" user_id=1
	 * wp give donors --create=1000
	 *
	 * @since       1.7
	 * @access		public
	 *
	 * @param		string $args        Command Data.
	 * @param		array  $assoc_args  List of command data.
	 *
	 * @return      void
	 *
	 * @subcommand  donors
	 */
	public function donors( $args, $assoc_args ) {}


	/**
	 * Create sample donation data for your Give site
	 *
	 * ## OPTIONS
	 *
	 * --number: The number of purchases to create
	 * --status=<status>: The status to create purchases as
	 * --id=<product_id>: A specific product to create purchase data for
	 * --price_id=<price_id>: A price ID of the specified product
	 *
	 * ## EXAMPLES
	 *
	 * wp give donations create --number=10 --status=completed
	 * wp give donations create --number=10 --id=103
	 *
	 * @since       1.7
	 * @access		public
	 *
	 * @param		string $args        Command Data.
	 * @param		array  $assoc_args  List of command data.
	 *
	 * @return      void
	 *
	 * @subcommand  donations
	 */
	public function donations( $args, $assoc_args ) {}

	/**
	 * Get give plugin report.
	 *
	 * ## OPTIONS
	 *
	 * [<formID>]
	 * : Donation form id, This can be used only if you want to see report for single form.
	 *
	 * [--type=<report_type>]
	 * : Name of report type, value of this parameter can be [form].
	 *
	 * @since 1.7
	 *
	 * @param		string $args        Command Data.
	 * @param		array  $assoc_args  List of command data.
	 *
	 * @subcommand report
	 *
	 * @return void
	 */
	public function report( $args, $assoc_args ) {}


	/**
	 * Delete cache (transient).
	 *
	 * ## OPTIONS
	 *
	 * [<formID>]
	 * : Donation form id, This can be used only if you want to delete stat cache for single form.
	 *
	 * [--type=<stat_type>]
	 * : Value of this parameter can be all (in case you want to delete all stat cache).
	 *
	 * ## EXAMPLES
	 *
	 *     # See form report
	 *     wp give cache delete
	 *
	 * @since  1.7
	 *
	 * @param		string $args        Command Data.
	 * @param		array  $assoc_args  List of command data.
	 *
	 * @return void
	 *
	 * @subcommand cache
	 */
	public function cache( $args, $assoc_args ) {
		// Bailout.
		if ( empty( $args[0] ) ) {
			WP_CLI::warning( __( 'Cache action is missing.', 'give' ) );
			return;
		}

		$action = $args[0];

		switch ( $action ) {
			case 'delete' :
				// Reset counter.
				self::$counter = 1;

				if ( $this->delete_all_stats() ) {
					// Report .eading.
					WP_CLI::success( 'All form stat transient cache deleted.' );
				} else {
					// Report .eading.
					WP_CLI::warning( 'We did not find any transient to delete :)' );
				}
				break;
		}

	}

	/**
	 * Delete all form stat transient
	 *
	 * @since  1.7
	 * @access private
	 *
	 * @return bool
	 */
	private function delete_all_stats() {
		global $wpdb;

		$stat_option_names = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT option_name FROM {$wpdb->options} where option_name LIKE '%%%s%%'",
				'_transient_give_stats_'
			),
			ARRAY_A
		);

		if ( ! empty( $stat_option_names ) ) {

			// Convert transient option name to transient name.
			$stat_option_names = array_map(
				function( $option ) {
					return str_replace( '_transient_', '', $option['option_name'] );
				},
				$stat_option_names
			);

			foreach ( $stat_option_names as $option_name ) {
				if ( delete_transient( $option_name ) ) {

					WP_CLI::log( $this->color_message( self::$counter . '. ', $option_name ) );
					self::$counter++;
				} else {
					WP_CLI::log( $this->color_message( __( 'Error while deleting this transient: ', 'give' ), $option_name ) );
				}
			}

			return true;
		}

		return false;
	}


	/**
	 * Return colored message
	 *
	 * @param string $heading Message heading.
	 * @param string $message Message content.
	 *
	 * @return mixed
	 */
	private function color_message( $heading, $message = '' ) {
		return WP_CLI::colorize( '%g' . $heading . '%n' ) . $message;
	}


	/**
	 * Output section heading.
	 *
	 * @since  1.7
	 * @access private
	 *
	 * @param string $heading Heading.
	 *
	 * @return void
	 */
	private function color_main_heading( $heading ) {
		WP_CLI::log( "\n----   " . $this->color_message( $heading ) . '   ----' );
	}

	/**
	 * Output section sub heading.
	 *
	 * @since  1.7
	 * @access private
	 *
	 * @param string $subheading Sub heading.
	 *
	 * @return void
	 */
	private function color_sub_heading( $subheading ) {
		WP_CLI::log( "\n" . $subheading . '' );
	}
}
