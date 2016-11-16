<?php
/**
 * GIVE WP_CLI commands
 *
 * @package give
 * @since   1.7
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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
	 * Get Give logo
	 *
	 * ## OPTIONS
	 *
	 * None. for a fun surprise.
	 *
	 * ## EXAMPLES
	 *
	 * wp give logo
	 *
	 * @since         1.7
	 * @access        public
	 *
	 * @param        string $args       Command Data.
	 * @param        array  $assoc_args List of command data.
	 *
	 * @return        void
	 *
	 * @subcommand    logo
	 */
	public function ascii( $args, $assoc_args ) {
		WP_CLI::log( file_get_contents( GIVE_PLUGIN_DIR . 'assets/images/give-ascii-logo.txt' ) );
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
	 * @since         1.7
	 * @access        public
	 *
	 * @param        string $args       Command Data.
	 * @param        array  $assoc_args List of command data.
	 *
	 * @return        void
	 *
	 * @subcommand    details
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
		WP_CLI::log( $this->color_message( __( 'Failed Donation Page: ', 'give' ) ) . ( $failure_page ? "[{$failure_page}] " . get_permalink( $failure_page ) : __( 'Not Set', 'give' ) ) );
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
		WP_CLI::log( $this->color_message( __( 'Test Mode: ', 'give' ), ( give_get_option( 'test_mode' ) ? __( 'Yes', 'give' ) : __( 'No', 'give' ) ) ) );
		WP_CLI::log( $this->color_message( __( 'Default Gateway: ', 'give' ), ( $default_gateway ? $default_gateway : __( 'Not Set', 'give' ) ) ) );

		// Payment gateways Information.
		$gateways = give_get_ordered_payment_gateways( give_get_payment_gateways() );
		WP_CLI::log( $this->color_message( __( 'Enabled Gateways: ', 'give' ) ) );

		if ( ! empty( $gateways ) ) {
			self::$counter = 1;
			foreach ( $gateways as $gateway ) {
				WP_CLI::log( '  ' . $this->color_message( self::$counter, $gateway['admin_label'] ) );
				self::$counter ++;
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
	 * [--id=<form_id>]
	 * : A specific form ID to retrieve
	 *
	 * [--number=<form_count>]
	 * : Number of form to retrieve
	 *
	 * ## EXAMPLES
	 *
	 * wp give forms
	 * wp give forms --id=103
	 * wp give forms --number=103
	 *
	 * @since         1.7
	 * @access        public
	 *
	 * @param        string $args       Command Data.
	 * @param        array  $assoc_args List of command data.
	 *
	 * @return        void
	 *
	 * @subcommand    forms
	 */
	public function forms( $args, $assoc_args ) {
		global $wp_query;
		$form_id = isset( $assoc_args ) && array_key_exists( 'id', $assoc_args ) ? absint( $assoc_args['id'] ) : false;
		$number  = isset( $assoc_args ) && array_key_exists( 'number', $assoc_args ) ? absint( $assoc_args['number'] ) : 10;
		$start   = time();

		// Cache previous number query var.
		$is_set_number = $cache_per_page = false;
		if ( isset( $wp_query->query_vars['number'] ) ) {
			$cache_per_page = $wp_query->query_vars['number'];
			$is_set_number  = true;
		}

		// Change number query var.
		$wp_query->query_vars['number'] = $number;

		// Get forms.
		$forms = $form_id ? $this->api->get_forms( $form_id ) : $this->api->get_forms();

		// Reset number query var.
		if ( $is_set_number ) {
			$wp_query->query_vars['number'] = $cache_per_page;
		}

		// Bailout.
		if ( array_key_exists( 'error', $forms ) ) {

			WP_CLI::warning( $forms['error'] );

			return;
		} elseif ( empty( $forms['forms'] ) ) {

			WP_CLI::error( __( 'No forms found.', 'give' ) );

			return;
		}

		// Param to check if form typeis already showed or not.
		$is_show_form_type = false;

		if ( 1 === count( $forms ) && $form_id ) {
			// Show single form.
			foreach ( $forms['forms'][0] as $key => $info ) {
				switch ( $key ) {
					case 'stats':
						$this->color_main_heading( ucfirst( $key ) );

						foreach ( $info as $heading => $data ) {
							$this->color_sub_heading( ucfirst( $heading ) );
							switch ( $heading ) {
								default:
									foreach ( $data as $subheading => $subdata ) {

										switch ( $subheading ) {
											case 'earnings':
												WP_CLI::log( $this->color_message( $subheading . ': ', give_currency_filter( $subdata ) ) );
												break;
											default:
												WP_CLI::log( $this->color_message( $subheading . ': ', $subdata ) );
										}
									}
							}
						}
						break;

					case 'pricing':
					case 'info':
					default:
						$this->color_main_heading( ucfirst( $key ) );

						// Show form type.
						if ( ! $is_show_form_type ) {
							$form              = new Give_Donate_Form( $form_id );
							$is_show_form_type = true;

							WP_CLI::log( $this->color_message( __( 'form type', 'give' ), $form->get_type() ) );
						}

						foreach ( $info as $heading => $data ) {

							switch ( $heading ) {
								case 'id':
									WP_CLI::log( $this->color_message( $heading, $data ) );
									break;

								default:
									$data = empty( $data ) ? __( 'Not set', 'give' ) : $data;
									WP_CLI::log( $this->color_message( $heading, $data ) );
							}
						}
				}
			}
		} else {
			// Show multiple form.
			$table_data             = array();
			$is_table_first_row_set = false;
			$table_column_count     = 0;

			WP_CLI::line( $this->color_message( sprintf( __( '%d donation forms found', 'give' ), count( $forms['forms'] ) ), '', false ) );

			foreach ( $forms['forms'] as $index => $form_data ) {

				// Default table data.
				$table_first_row = array();
				$table_row       = array();

				foreach ( $form_data['info'] as $key => $form ) {

					// Do not show thumbnail, content and link in table.
					if ( in_array( $key, array( 'content', 'thumbnail', 'link' ), true ) ) {
						continue;
					}

					if ( ! $is_table_first_row_set ) {
						$table_first_row[] = $key;
					}

					$table_row[] = $form;

					if ( 'status' === $key ) {
						// First array item will be an form id in our case.
						$form = new Give_Donate_Form( absint( $table_row[0] ) );

						$table_row[] = $form->get_type();
					}
				}

				// Set table first row.
				if ( ! $is_table_first_row_set ) {

					// Add extra column to table.
					$table_first_row[] = 'type';

					$table_data[]           = $table_first_row;
					$is_table_first_row_set = true;
				}

				// set table data.
				$table_data[] = $table_row;
			}

			$this->display_table( $table_data );
		}
	}


	/**
	 * Get the donors currently on your Give site. Can also be used to create donors records
	 *
	 * ## OPTIONS
	 *
	 * [--id=<donor_id>]
	 * : A specific donor ID to retrieve
	 *
	 * [--email=<donor_email>]
	 * : The email address of the donor to retrieve
	 *
	 * [--number=<donor_count>]
	 * : The number of donor to retrieve
	 *
	 * [--create=<number>]
	 * : The number of arbitrary donors to create. Leave as 1 or blank to create a
	 * donor with a specific email
	 *
	 * [--form-id=<donation_form_id>]
	 * : Get list of donors of specific donation form
	 *
	 * [--name=<name_of_donor>]
	 * : Name with which you want to create new donor
	 *
	 * [--format=<output_format>]
	 * : In which format you want to see results. Valid formats: table, json, csv
	 *
	 * ## EXAMPLES
	 *
	 * wp give donors --id=103
	 * wp give donors --email=john@test.com
	 * wp give donors --create=1 --email=john@test.com
	 * wp give donors --create=1 --email=john@test.com --name="John Doe"
	 * wp give donors --create=1000
	 * wp give donors --number=1000
	 * wp give donors --form-id=1024
	 *
	 * @since         1.7
	 * @access        public
	 *
	 * @param        string $args       Command Data.
	 * @param        array  $assoc_args List of command data.
	 *
	 * @return        void
	 *
	 * @subcommand    donors
	 */
	public function donors( $args, $assoc_args ) {
		global $wp_query;
		$donor_id = isset( $assoc_args ) && array_key_exists( 'id', $assoc_args ) ? absint( $assoc_args['id'] ) : false;
		$email    = isset( $assoc_args ) && array_key_exists( 'email', $assoc_args ) ? $assoc_args['email'] : false;
		$name     = isset( $assoc_args ) && array_key_exists( 'name', $assoc_args ) ? $assoc_args['name'] : '';
		$create   = isset( $assoc_args ) && array_key_exists( 'create', $assoc_args ) ? $assoc_args['create'] : false;
		$number   = isset( $assoc_args ) && array_key_exists( 'number', $assoc_args ) ? $assoc_args['number'] : 10;
		$form_id  = isset( $assoc_args ) && array_key_exists( 'form-id', $assoc_args ) ? $assoc_args['form-id'] : 0;
		$format   = isset( $assoc_args ) && array_key_exists( 'format', $assoc_args ) ? $assoc_args['format'] : 'table';
		$start    = time();

		if ( $create ) {
			$number = 1;

			if ( isset( $assoc_args['email'] ) && ! is_email( $email ) ) {
				WP_CLI::warning( 'Wrong email address provided.', 'give' );

				return;
			}

			// Create one or more donors.
			if ( ! $email ) {
				// If no email is specified, look to see if we are generating arbitrary donor accounts.
				$number = is_numeric( $create ) ? absint( $create ) : 1;
			}

			for ( $i = 0; $i < $number; $i ++ ) {
				if ( ! $email ) {

					// Generate fake email.
					$email = 'customer-' . uniqid() . '@test.com';
				}

				$args = array(
					'email' => $email,
					'name'  => $name,
				);

				$customer_id = Give()->customers->add( $args );

				if ( $customer_id ) {
					WP_CLI::line( $this->color_message( sprintf( __( 'Donor #%d created successfully', 'give' ), $customer_id ) ) );
				} else {
					WP_CLI::error( __( 'Failed to create donor', 'give' ) );
				}

				// Reset email to false so it is generated on the next loop (if creating donors).
				$email = false;
			}

			WP_CLI::line( $this->color_message( sprintf( __( '%1$d donors created in %2$d seconds', 'give' ), $number, time() - $start ) ) );

		} else {
			// Counter.
			self::$counter = 1;

			// Search for customers.
			$search = $donor_id ? $donor_id : $email;

			/**
			 * Get donors.
			 */
			// Cache previous number query var.
			$is_set_number = $cache_per_page = false;
			if ( isset( $wp_query->query_vars['number'] ) ) {
				$cache_per_page = $wp_query->query_vars['number'];
				$is_set_number  = true;
			}

			// Change number query var.
			$wp_query->query_vars['number'] = $number;

			// Get donors.
			if ( $form_id ) {
				// @TODO: Allow user to get a list of donors by donation status.
				$donors = $this->get_donors_by_form_id( $form_id );
			} else {
				$donors = $this->api->get_customers( $search );
			}

			// Reset number query var.
			if ( $is_set_number ) {
				$wp_query->query_vars['number'] = $cache_per_page;
			}

			if ( isset( $donors['error'] ) ) {
				WP_CLI::error( $donors['error'] );
			}

			if ( empty( $donors ) ) {
				WP_CLI::error( __( 'No donors found.', 'give' ) );

				return;
			}

			$table_data             = array();
			$is_table_first_row_set = false;

			foreach ( $donors['donors'] as $donor_data ) {
				// Set default table row data.
				$table_first_row = array( __( 's_no', 'give' ) );
				$table_row       = array( self::$counter );

				foreach ( $donor_data as $key => $donor ) {
					switch ( $key ) {
						case 'stats':
							foreach ( $donor as $heading => $data ) {

								// Get first row.
								if ( ! $is_table_first_row_set ) {
									$table_first_row[] = $heading;
								}

								switch ( $heading ) {
									case 'total_spent':
										$table_row[] = give_currency_filter( $data );
										break;

									default:
										$table_row[] = $data;
								}
							}
							break;

						case 'info':
						default:
							foreach ( $donor as $heading => $data ) {

								// Get first row.
								if ( ! $is_table_first_row_set ) {
									$table_first_row[] = $heading;
								}

								$table_row[] = $data;
							}
					}
				}

				// Add first row data to table data.
				if ( ! $is_table_first_row_set ) {
					$table_data[]           = $table_first_row;
					$is_table_first_row_set = true;
				}

				// Add table row data.
				$table_data[] = $table_row;

				// Increase counter.
				self::$counter ++;
			}

			switch ( $format ) {
				case 'json':
					$table_column_name = $table_data[0];
					unset( $table_data[0] );

					$new_table_data = array();
					foreach ( $table_data as $index => $data ) {
						foreach ( $data as $key => $value ) {
							$new_table_data[ $index ][ $table_column_name[ $key ] ] = $value;
						}
					}

					WP_CLI::log( json_encode( $new_table_data ) );
					break;

				case 'csv':
					$file_path = trailingslashit( WP_CONTENT_DIR ) . 'uploads/give_donors_' . date( 'Y_m_d_s', current_time( 'timestamp' ) ) . '.csv';
					$fp        = fopen( $file_path, 'w' );

					if ( is_writable( $file_path ) ) {
						foreach ( $table_data as $fields ) {
							fputcsv( $fp, $fields );
						}

						fclose( $fp );

						WP_CLI::success( "Donors list csv created successfully: {$file_path}" );
					} else {
						WP_CLI::warning( "Unable to create donors list csv file: {$file_path} (May folder do not have write permission)" );
					}

					break;

				default:
					$this->display_table( $table_data );
			}
		}
	}


	/**
	 * Get the recent donations for your Give site
	 *
	 * ## OPTIONS
	 *
	 * [--number=<donation_count>]
	 * : The number of donations to retrieve
	 *
	 *
	 * ## EXAMPLES
	 *
	 * wp give donations
	 * wp give donations --number=100
	 *
	 * @since         1.7
	 * @access        public
	 *
	 * @param        string $args       Command Data.
	 * @param        array  $assoc_args List of command data.
	 *
	 * @return        void
	 *
	 * @subcommand    donations
	 */
	public function donations( $args, $assoc_args ) {
		global $wp_query;
		$number = isset( $assoc_args ) && array_key_exists( 'number', $assoc_args ) ? $assoc_args['number'] : 10;

		// Cache previous number query var.
		$is_set_number = $cache_per_page = false;
		if ( isset( $wp_query->query_vars['number'] ) ) {
			$cache_per_page = $wp_query->query_vars['number'];
			$is_set_number  = true;
		}

		// Change number query var.
		$wp_query->query_vars['number'] = $number;

		// Get donations.
		$donations = $this->api->get_recent_donations();

		// Reset number query var.
		if ( $is_set_number ) {
			$wp_query->query_vars['number'] = $cache_per_page;
		}

		if ( empty( $donations ) ) {
			WP_CLI::error( __( 'No donations found.', 'give' ) );

			return;
		}

		self::$counter = 1;

		foreach ( $donations['donations'] as $key => $donation ) {
			$this->color_main_heading( sprintf( __( '%1$s. Donation #%2$s', 'give' ), self::$counter, $donation['ID'] ), 'Y' );
			self::$counter ++;

			foreach ( $donation as $column => $data ) {

				if ( is_array( $data ) ) {
					$this->color_sub_heading( $column );
					foreach ( $data as $subcolumn => $subdata ) {

						// Decode html codes.
						switch ( $subcolumn ) {
							case 'name':
								$subdata = html_entity_decode( $subdata );
								break;
						}

						// @TODO Check if multi dimension array information is importent to show or not. For example inside donation array we have array for fees data inside payment meta.
						if ( is_array( $subdata ) ) {
							continue;
						}

						WP_CLI::log( $this->color_message( $subcolumn, $subdata ) );
					}
					continue;
				}

				WP_CLI::log( $this->color_message( $column, $data ) );
			}
		}
	}

	/**
	 * Get give plugin report.
	 *
	 * ## OPTIONS
	 *
	 * [--id=<donation_form_id>]
	 * : The ID of a specific donation_form to retrieve stats for, or all
	 *
	 * [--date=<range|this_month|last_month|today|yesterday|this_quarter|last_quarter|this_year|last_year>]
	 * : A specific date range to retrieve stats for
	 *
	 * [--start-date=<date>]
	 * : The start date of a date range to retrieve stats for
	 *
	 * [--end-date=<date>]
	 * : The end date of a date range to retrieve stats for
	 *
	 * ## EXAMPLES
	 *
	 * wp give report --date=this_month
	 * wp give report --start-date=01/02/2014 --end-date=02/23/2014
	 * wp give report --date=last_year
	 * wp give report --date=last_year --id=15
	 *
	 * @since         1.7
	 * @access        public
	 *
	 * @param        string $args       Command Data.
	 * @param        array  $assoc_args List of command data.
	 *
	 * @subcommand    report
	 *
	 * @return        void
	 */
	public function report( $args, $assoc_args ) {
		$stats      = new Give_Payment_Stats();
		$date       = isset( $assoc_args ) && array_key_exists( 'date', $assoc_args ) ? $assoc_args['date'] : false;
		$start_date = isset( $assoc_args ) && array_key_exists( 'start-date', $assoc_args ) ? $assoc_args['start-date'] : false;
		$end_date   = isset( $assoc_args ) && array_key_exists( 'end-date', $assoc_args ) ? $assoc_args['end-date'] : false;
		$form_id    = isset( $assoc_args ) && array_key_exists( 'id', $assoc_args ) ? $assoc_args['id'] : 0;

		if ( ! empty( $date ) ) {
			$start_date = $date;
			$end_date   = false;
		} elseif ( empty( $date ) && empty( $start_date ) ) {
			$start_date = 'this_month';
			$end_date   = false;
		}

		// Get stats.
		$earnings = $stats->get_earnings( $form_id, $start_date, $end_date );
		$sales    = $stats->get_sales( $form_id, $start_date, $end_date );

		WP_CLI::line( $this->color_message( __( 'Earnings', 'give' ), give_currency_filter( $earnings ) ) );
		WP_CLI::line( $this->color_message( __( 'Sales', 'give' ), $sales ) );
	}


	/**
	 * Delete cache (transient).
	 *
	 * ## OPTIONS
	 *
	 * [--action=<cache_action>]
	 * : Value of this parameter can be delete (in case you want to delete all stat cache).
	 *
	 * ## EXAMPLES
	 *
	 *    # See form report
	 *    wp give cache --action=delete
	 *
	 * @since         1.7
	 * @access        public
	 *
	 * @param        string $args       Command Data.
	 * @param        array  $assoc_args List of command data.
	 *
	 * @return        void
	 *
	 * @subcommand    cache
	 */
	public function cache( $args, $assoc_args ) {
		$action = isset( $assoc_args ) && array_key_exists( 'action', $assoc_args ) ? $assoc_args['action'] : false;

		// Bailout.
		if ( ! $action || ! in_array( $action, array( 'delete' ), true ) ) {
			WP_CLI::warning( __( 'Type wp give cache --action=delete to delete all stat transients', 'give' ) );

			return;
		}

		switch ( $action ) {
			case 'delete' :
				// Reset counter.
				self::$counter = 1;

				if ( $this->delete_stats_transients() ) {
					// Report .eading.
					WP_CLI::success( 'Give cache deleted.' );
				} else {
					// Report .eading.
					WP_CLI::warning( 'We did not find any Give plugin cache to delete :)' );
				}
				break;
		}

	}

	/**
	 * Delete all form stat transient
	 *
	 * @since     1.7
	 * @access    private
	 *
	 * @return    bool
	 */
	private function delete_stats_transients() {
		global $wpdb;

		$stat_option_names = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT option_name FROM {$wpdb->options} where (option_name LIKE '%%%s%%' OR option_name LIKE '%%%s%%')",
				array(
					'_transient_give_stats_',
					'give_cache',
				)
			),
			ARRAY_A
		);

		if ( ! empty( $stat_option_names ) ) {

			foreach ( $stat_option_names as $option_name ) {
				$error       = false;
				$option_name = $option_name['option_name'];

				switch ( true ) {
					case ( false !== strpos( $option_name, 'transient' ) ):
						$option_name = str_replace( '_transient_', '', $option_name );
						$error       = delete_transient( $option_name );
						break;

					default:
						$error = delete_option( $option_name );
				}

				if ( $error ) {
					WP_CLI::log( $this->color_message( self::$counter, $option_name ) );
					self::$counter ++;
				} else {
					WP_CLI::log( $this->color_message( __( 'Error while deleting this transient', 'give' ), $option_name ) );
				}
			}

			return true;
		}

		return false;
	}


	/**
	 * Return colored message
	 *
	 * @param    string $heading Message heading.
	 * @param    string $message Message content.
	 * @param    bool   $colon   Check if add colon between heading and message.
	 * @param    string $color   Heading color.
	 *
	 * @return    mixed
	 */
	private function color_message( $heading, $message = '', $colon = true, $color = 'g' ) {
		// Add colon.
		if ( $colon ) {
			$heading = $heading . ': ';
		}

		return WP_CLI::colorize( "%{$color}" . $heading . '%n' ) . $message;
	}


	/**
	 * Output section heading.
	 *
	 * @since     1.7
	 * @access    private
	 *
	 * @param    string $heading Heading.
	 * @param    string $color   Color.
	 *
	 * @return    void
	 */
	private function color_main_heading( $heading, $color = 'g' ) {
		WP_CLI::log( "\n######   " . $this->color_message( $heading, '', false, $color ) . '   ######' );
	}

	/**
	 * Output section sub heading.
	 *
	 * @since     1.7
	 * @access    private
	 *
	 * @param    string $subheading Sub heading.
	 *
	 * @return    void
	 */
	private function color_sub_heading( $subheading ) {
		WP_CLI::log( "\n--->" . $subheading . '', '', false );
	}


	/**
	 * Display data in table format.
	 *
	 * @since     1.7
	 * @access    private
	 *
	 * @param    array $data Array of table data.
	 *
	 * @return    void
	 */
	private function display_table( $data ) {
		$table = new \cli\Table();

		// Set table header.
		$table->setHeaders( $data[0] );

		// Remove table header.
		unset( $data[0] );

		// Set table data.
		$table->setRows( $data );

		// Display table.
		$table->display();
	}


	/**
	 * Get donors by form id
	 *
	 * @since 1.8
	 *
	 * @param int $form_id From id.
	 *
	 * @return array
	 */

	private function get_donors_by_form_id( $form_id ) {
		$donors = array();

		$donations = new Give_Payments_Query(
			array(
				'give_forms' => array( $form_id ),
				'number'     => - 1,
				'status'     => array( 'publish' ),
			)
		);

		$donations   = $donations->get_payments();
		$skip_donors = array();

		/* @var Give_Payment|object $donation Payment object. */
		foreach ( $donations as $donation ) {

			if ( in_array( $donation->customer_id, $skip_donors ) ) {
				continue;
			}

			if ( ! empty( $donors ) ) {
				$donors['donors'][] = current( current( $this->api->get_customers( (int) $donation->customer_id ) ) );
			} else {
				$donors = array_merge( $donors, $this->api->get_customers( (int) $donation->customer_id ) );
			}

			$skip_donors[] = $donation->customer_id;
		}

		return $donors;
	}
}
