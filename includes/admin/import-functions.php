<?php
/**
 * Import Functions
 *
 * @package     Give
 * @subpackage  Functions
 * @copyright   Copyright (c) 2016, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.8.14
 */

use Give\Donations\ValueObjects\DonationMetaKeys;
use Give\Framework\Database\DB;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get the Import report of the donations
 *
 * @since 1.8.13
 */
function give_import_donation_report() {
	return get_option( 'give_import_donation_report', [] );
}


/**
 * Update the Import report of the donations
 *
 * @since 1.8.13
 */
function give_import_donation_report_update( $value = [] ) {
	update_option( 'give_import_donation_report', $value, false );
}

/**
 * Delete the Import report of the donations
 *
 * @since 1.8.13
 */
function give_import_donation_report_reset() {
	update_option( 'give_import_donation_report', [], false );
}

/**
 * Get the Import report of subscriptions
 *
 * @unreleased
 */
function give_import_subscription_report() {
    return get_option( 'give_import_subscription_report', [] );
}

/**
 * Update the Import report of subscriptions
 *
 * @unreleased
 */
function give_import_subscription_report_update( $value = [] ) {
    update_option( 'give_import_subscription_report', $value, false );
}

/**
 * Delete the Import report of subscriptions
 *
 * @unreleased
 */
function give_import_subscription_report_reset() {
    update_option( 'give_import_subscription_report', [], false );
}

/**
 * Give get form data from csv if not then create and form and return the form value.
 *
 * @since      1.8.13.
 * @since 2.26.0 Replace deprecated get_page_by_title() with give_get_page_by_title().
 *
 * @param $data .
 *
 * @return array|bool|Give_Donate_Form|int|null|WP_Post
 */
function give_import_get_form_data_from_csv( $data, $import_setting = [] ) {
	$new_form = false;
	$dry_run  = isset( $import_setting['dry_run'] ) ? $import_setting['dry_run'] : false;

	// Get the import report
	$report = give_import_donation_report();

	$form = false;
	$meta = [];

	if ( ! empty( $data['form_id'] ) ) {
		$form = new Give_Donate_Form( $data['form_id'] );
		// Add support to older php version.
		$form_id = $form->get_ID();
		if ( empty( $form_id ) ) {
			$form = false;
		} else {
			$report['duplicate_form'] = ( ! empty( $report['duplicate_form'] ) ? ( absint( $report['duplicate_form'] ) + 1 ) : 1 );
		}
	}

	if ( false === $form && ! empty( $data['form_title'] ) ) {
		$form = give_get_page_by_title($data['form_title'], OBJECT, 'give_forms');

		if ( ! empty( $form->ID ) ) {

			$report['duplicate_form'] = ( ! empty( $report['duplicate_form'] ) ? ( absint( $report['duplicate_form'] ) + 1 ) : 1 );

			$form = new Give_Donate_Form( $form->ID );
		} else {
			$form = new Give_Donate_Form();
			$args = [
				'post_title'  => $data['form_title'],
				'post_status' => 'publish',
			];

			if ( empty( $dry_run ) ) {
				$form = $form->create( $args );
			}

			$report['create_form'] = ( ! empty( $report['create_form'] ) ? ( absint( $report['create_form'] ) + 1 ) : 1 );
			$new_form              = true;

		}

        $form = give_get_page_by_title($data['form_title'], OBJECT, 'give_forms');
		if ( ! empty( $form->ID ) ) {
			$form = new Give_Donate_Form( $form->ID );
		}
	}

	if ( ! empty( $form ) && $form->get_ID() && ! empty( $data['form_level'] ) && empty( $dry_run ) ) {

		$price_option = 'set';
		$form_level   = strtolower( preg_replace( '/\s+/', '', $data['form_level'] ) );

		if ( 'custom' !== $form_level ) {
			$prices     = (array) $form->get_prices();
			$price_text = [];
			foreach ( $prices as $key => $price ) {
				if ( isset( $price['_give_id']['level_id'] ) ) {
					$price_text[ $price['_give_id']['level_id'] ] = ( ! empty( $price['_give_text'] ) ? strtolower( preg_replace( '/\s+/', '', $price['_give_text'] ) ) : '' );
				}
			}

			if ( ! in_array( $form_level, $price_text ) ) {

				// For generating unquiet level id.
				$count     = 1;
				$new_level = count( $prices ) + $count;
				while ( array_key_exists( $new_level, $price_text ) ) {
					$count ++;
					$new_level = count( $prices ) + $count;
				}

				$multi_level_donations = [
					[
						'_give_id'     => [
							'level_id' => $new_level,
						],
						'_give_amount' => give_sanitize_amount_for_db( $data['amount'] ),
						'_give_text'   => $data['form_level'],
					],
				];

				$price_text[ $new_level ] = strtolower( preg_replace( '/\s+/', '', $data['form_level'] ) );

				if ( ! empty( $prices ) && is_array( $prices ) && ! empty( $prices[0] ) ) {
					// Sort $prices by amount in ascending order.
					$prices = wp_list_sort( $prices, '_give_amount', 'ASC' );
				} else {
					$prices = $multi_level_donations;
				}

				// Unset _give_default key from $prices.
				foreach ( $prices as $key => $price ) {
					if ( isset( $prices[ $key ]['_give_default'] ) ) {
						unset( $prices[ $key ]['_give_default'] );
					}
				}

				// Set the first $price of the $prices as default.
				$prices[0]['_give_default'] = 'default';
			}
			$form->price_id = array_search( $form_level, $price_text );

			$donation_levels_amounts = wp_list_pluck( $prices, '_give_amount' );
			$min_amount              = min( $donation_levels_amounts );
			$max_amount              = max( $donation_levels_amounts );

			$meta = [
				'_give_levels_minimum_amount' => $min_amount,
				'_give_levels_maximum_amount' => $max_amount,
				'_give_donation_levels'       => array_values( $prices ),
			];

			$price_option = 'multi';
		} else {
			$form->price_id = 'custom';
		}

		$defaults = [
			'_give_set_price' => give_sanitize_amount_for_db( $data['amount'] ),
		];

		// If new form is created.
		if ( ! empty( $new_form ) ) {
			$new_form = [
				'_give_custom_amount_text'          => ( ! empty( $data['form_custom_amount_text'] ) ? $data['form_custom_amount_text'] : __( 'Custom Amount', 'give' ) ),
				'_give_logged_in_only'              => 'enabled',
				'_give_custom_amount'               => 'enabled',
				'_give_payment_import'              => true,
				'_give_display_style'               => 'radios',
				'_give_payment_display'             => 'onpage',
				'give_product_notes'                => 'Donation Notes',
				'_give_product_type'                => 'default',
				'_give_default_gateway'             => 'global',
				'_give_customize_offline_donations' => 'global',
				'_give_show_register_form'          => 'both',
				'_give_price_option'                => $price_option,
			];
			$defaults = wp_parse_args( $defaults, $new_form );
		}

		$meta = wp_parse_args( $meta, $defaults );

		foreach ( $meta as $key => $value ) {
			give_update_meta( $form->get_ID(), $key, $value );
		}
	}

	// update the report
	give_import_donation_report_update( $report );

	return $form;
}

/**
 * Give get user details if not then create a user. Used in Import Donation CSV.
 *
 * @since 1.8.13
 *
 * @param $data
 *
 * @return bool|false|WP_User
 */
function give_import_get_user_from_csv( $data, $import_setting = [] ) {
	$report               = give_import_donation_report();
	$dry_run              = isset( $import_setting['dry_run'] ) ? $import_setting['dry_run'] : false;
	$dry_run_donor_create = false;
	$donor_data           = [];
	$donor_id             = false;

	// check if donor id is not empty
	if ( ! empty( $data['donor_id'] ) ) {
		$donor_data = new Give_Donor( (int) $data['donor_id'] );
		if ( ! empty( $donor_data->id ) ) {
			$report['duplicate_donor'] = ( ! empty( $report['duplicate_donor'] ) ? ( absint( $report['duplicate_donor'] ) + 1 ) : 1 );
		}
	}

	if ( empty( $donor_data->id ) && ! empty( $data['user_id'] ) ) {
		$user_id    = (int) $data['user_id'];
		$donor_data = new Give_Donor( $user_id, true );

		if ( empty( $donor_data->id ) ) {
			$donor_data = get_user_by( 'id', $user_id );

			// if no wp user is found then no donor is create with that user id
			if ( ! empty( $donor_data->ID ) ) {

				if ( empty( $dry_run ) ) {
					$first_name = ( ! empty( $data['first_name'] ) ? $data['first_name'] : $donor_data->user_nicename );
					$last_name  = ( ! empty( $data['last_name'] ) ? $data['last_name'] : ( ( $lastname = get_user_meta( $donor_data->ID, 'last_name', true ) ) ? $lastname : '' ) );
					$name       = $first_name . ' ' . $last_name;
					$user_email = $donor_data->user_email;
					$donor_args = [
						'name'    => $name,
						'email'   => $user_email,
						'user_id' => $user_id,
					];

					$donor_data = new Give_Donor();
					$donor_data->create( $donor_args );

					// Adding notes that donor is being imported from CSV.
					$current_user = wp_get_current_user();
					$donor_data->add_note( wp_sprintf( __( 'This donor was imported by %s', 'give' ), $current_user->user_email ) );

					// Add is used to ensure duplicate emails are not added
					if ( $user_email != $data['email'] && ! empty( $data['email'] ) ) {
						$donor_data->add_meta( 'additional_email', $data['email'] );
					}
				} else {
					$dry_run_donor_create = true;
					$donor_data           = [ 'id' => 1 ];
				}

				$report['create_donor'] = ( ! empty( $report['create_donor'] ) ? ( absint( $report['create_donor'] ) + 1 ) : 1 );
			} elseif ( $dry_run ) {
				$donor_data = [];
			}
		} else {
			// Add is used to ensure duplicate emails are not added
			if ( $donor_data->email != $data['email'] && empty( $dry_run ) ) {
				$donor_data->add_meta( 'additional_email', ( ! empty( $data['email'] ) ? $data['email'] : $donor_data->email ) );
			}
			$report['duplicate_donor'] = ( ! empty( $report['duplicate_donor'] ) ? ( absint( $report['duplicate_donor'] ) + 1 ) : 1 );
		}
	}

	if ( empty( $donor_data->id ) && ! empty( $data['email'] ) && empty( $dry_run_donor_create ) ) {

		$donor_data = new Give_Donor( $data['email'] );
		if ( empty( $donor_data->id ) ) {
			$donor_data = get_user_by( 'email', $data['email'] );

			if ( empty( $donor_data->ID ) && isset( $import_setting['create_user'] ) && 1 === absint( $import_setting['create_user'] ) ) {
				$data['first_name'] = ( ! empty( $data['first_name'] ) ? $data['first_name'] : $data['email'] );
				$data['last_name']  = ( ! empty( $data['last_name'] ) ? $data['last_name'] : '' );
				$give_role          = (array) give_get_option( 'donor_default_user_role', get_option( 'default_role', ( ( $give_donor = wp_roles()->is_role( 'give_donor' ) ) && ! empty( $give_donor ) ? 'give_donor' : 'subscriber' ) ) );
				$donor_args         = [
					'user_login'      => $data['email'],
					'user_email'      => $data['email'],
					'user_registered' => date( 'Y-m-d H:i:s' ),
					'user_first'      => $data['first_name'],
					'user_last'       => $data['last_name'],
					'user_pass'       => wp_generate_password( 8, true ),
					'role'            => $give_role,
				];

				/**
				 * Filter to modify user data before new user id register.
				 *
				 * @since 1.8.13
				 */
				$donor_args = (array) apply_filters( 'give_import_insert_user_args', $donor_args, $data, $import_setting );

				if ( empty( $dry_run ) ) {

					// This action was added to remove the login when using the give register function.
					add_filter( 'give_log_user_in_on_register', 'give_log_user_in_on_register_callback', 11 );
					$donor_id = give_register_and_login_new_user( $donor_args );
					remove_filter( 'give_log_user_in_on_register', 'give_log_user_in_on_register_callback', 11 );

					$donor_data = new Give_Donor( $donor_id, true );
					$donor_data->update_meta( '_give_payment_import', true );

				} else {
					$dry_run_donor_create   = true;
					$report['create_donor'] = ( ! empty( $report['create_donor'] ) ? ( absint( $report['create_donor'] ) + 1 ) : 1 );
				}
			} else {
				$donor_id = ( ! empty( $donor_data->ID ) ? $donor_data->ID : false );
			}

			if ( empty( $dry_run_donor_create ) && ( ! empty( $donor_id ) || ( isset( $import_setting['create_user'] ) && 0 === absint( $import_setting['create_user'] ) ) ) ) {
				$donor_data = new Give_Donor( $donor_id, true );

				if ( empty( $donor_data->id ) ) {

					if ( ! empty( $data['form_id'] ) ) {
						$form = new Give_Donate_Form( $data['form_id'] );
					}

					if ( empty( $dry_run ) ) {
						$payment_title = ( isset( $data['form_title'] ) ? $data['form_title'] : ( isset( $form ) ? $form->get_name() : __( 'New Form', 'give' ) ) );
						$donor_args    = [
							'name'  => ! is_email( $payment_title ) ? $data['first_name'] . ' ' . $data['last_name'] : '',
							'email' => $data['email'],
						];
						if ( ! empty( $donor_id ) ) {
							$donor_args['user_id'] = $donor_id;
						}
						$donor_data->create( $donor_args );

						// Adding notes that donor is being imported from CSV.
						$current_user = wp_get_current_user();
						$donor_data->add_note( wp_sprintf( __( 'This donor was imported by %s', 'give' ), $current_user->user_email ) );
					} else {
						$dry_run_donor_create = true;
					}
					$report['create_donor'] = ( ! empty( $report['create_donor'] ) ? ( absint( $report['create_donor'] ) + 1 ) : 1 );
				} else {
					$report['duplicate_donor'] = ( ! empty( $report['duplicate_donor'] ) ? ( absint( $report['duplicate_donor'] ) + 1 ) : 1 );
				}
			}
		} else {
			$report['duplicate_donor'] = ( ! empty( $report['duplicate_donor'] ) ? ( absint( $report['duplicate_donor'] ) + 1 ) : 1 );
		}
	}

	// update the report
	give_import_donation_report_update( $report );

	return $donor_data;
}

/**
 * Return the option that are default options.
 *
 * @since 1.8.13
 */
function give_import_default_options() {
	/**
	 * Filter to modify default option in the import dropdown
	 *
	 * @since 1.8.13
	 *
	 * @return array
	 */
	return (array) apply_filters(
		'give_import_default_options',
		[
			'' => __( 'Do not import', 'give' ),
		]
	);
}

/**
 * Return the option that are related to donations.
 *
 * @since 1.8.13
 */
function give_import_donations_options() {
	/**
	 * Filter to modify donations option in the import dropdown
	 *
	 * @since 4.5.0 Add gateway transaction id option
	 * @since 1.8.13
	 *
	 * @return array
	 */
	return (array) apply_filters(
		'give_import_donations_options',
		[
			'id'           => __( 'Donation ID', 'give' ),
			'amount'       => [
				__( 'Donation Amount', 'give' ),
				__( 'Amount', 'give' ),
				__( 'Donation Total', 'give' ),
				__( 'Total', 'give' ),
			],
			'currency'     => [
				__( 'Donation Currencies', 'give' ),
				__( 'Currencies', 'give' ),
				__( 'Currencies Code', 'give' ),
				__( 'Currency Code', 'give' ),
				__( 'Code', 'give' ),
			],
			'post_date'    => [
				__( 'Donation Date', 'give' ),
				__( 'Date', 'give' ),
			],
			'post_time'    => [
				__( 'Donation Time', 'give' ),
				__( 'Time', 'give' ),
			],
			'title_prefix' => [
				__( 'Title Prefix', 'give' ),
				__( 'Prefix', 'give' ),
			],
			'first_name'   => [
				__( 'Donor First Name', 'give' ),
				__( 'First Name', 'give' ),
				__( 'Name', 'give' ),
				__( 'First', 'give' ),
			],
			'last_name'    => [
				__( 'Donor Last Name', 'give' ),
				__( 'Last Name', 'give' ),
				__( 'Last', 'give' ),
			],
			'company_name' => [
				__( 'Company Name', 'give' ),
				__( 'Donor Company Name', 'give' ),
				__( 'Donor Company', 'give' ),
				__( 'Company', 'give' ),
			],
			'line1'        => [
				__( 'Address 1', 'give' ),
				__( 'Address', 'give' ),
			],
			'line2'        => __( 'Address 2', 'give' ),
			'city'         => __( 'City', 'give' ),
			'state'        => [
				__( 'State', 'give' ),
				__( 'Province', 'give' ),
				__( 'County', 'give' ),
				__( 'Region', 'give' ),
			],
			'country'      => __( 'Country', 'give' ),
			'zip'          => [
				__( 'Zip Code', 'give' ),
				__( 'Zip', 'give' ),
				__( 'zipcode', 'give' ),
				__( 'Postal Code', 'give' ),
				__( 'Postal', 'give' ),
			],
			'email'        => [
				__( 'Donor Email', 'give' ),
				__( 'Email', 'give' ),
				__( 'Email Address', 'give' ),
			],
			'post_status'  => [
				__( 'Donation Status', 'give' ),
				__( 'Status', 'give' ),
			],
			'gateway'      => [
				__( 'Payment Method', 'give' ),
				__( 'Method', 'give' ),
				__( 'Payment Gateway', 'give' ),
				__( 'Gateway', 'give' ),
			],
			'gateway_transaction_id' => [
				__( 'Transaction ID', 'give' ),
				__( 'Gateway Transaction ID', 'give' ),
				__( 'Transaction Identifier', 'give' ),
			],
			'notes'        => __( 'Notes', 'give' ),
			'mode'         => [
				__( 'Payment Mode', 'give' ),
				__( 'Mode', 'give' ),
				__( 'Test Mode', 'give' ),
			],
			'donor_ip'     => __( 'Donor IP Address', 'give' ),
			'post_meta'    => __( 'Import as Meta', 'give' ),
		]
	);
}

/**
 * Return the option that are related to donations.
 *
 * @since 1.8.13
 */
function give_import_donor_options() {
	/**
	 * Filter to modify donors option in the import dropdown
	 *
	 * @since 1.8.13
	 *
	 * @return array
	 */
	return (array) apply_filters(
		'give_import_donor_options',
		[
			'donor_id' => __( 'Donor ID', 'give' ),
			'user_id'  => __( 'User ID', 'give' ),
		]
	);
}

/**
 * Return the option that are related to donations.
 *
 * @since 1.8.13
 */
function give_import_donation_form_options() {
	/**
	 * Filter to modify form option in the import dropdown
	 *
	 * @since 1.8.13
	 *
	 * @return array
	 */
	return (array) apply_filters(
		'give_import_donation_form_options',
		[
			'form_title'              => [
				__( 'Donation Form Title', 'give' ),
				__( 'Donation Form', 'give' ),
				__( 'Form Name', 'give' ),
				__( 'Title', 'give' ),
				__( 'Form Title', 'give' ),
				'ignore' => [
					__( 'Title Prefix', 'give' ),
					__( 'Prefix', 'give' ),
				],
			],
			'form_id'                 => [
				__( 'Donation Form ID', 'give' ),
				__( 'Form ID', 'give' ),
			],
			'form_level'              => [
				__( 'Donation Level', 'give' ),
				__( 'Level', 'give' ),
				__( 'Level Title', 'give' ),
			],
			'form_custom_amount_text' => __( 'Custom Amount Text', 'give' ),
		]
	);
}

/**
 * Return the options related to subscription import mapping (model properties)
 *
 * Keys intentionally match Subscription model properties and related fields
 *
 * @unreleased
 */
function give_import_subscription_options() {
    return (array) apply_filters(
        'give_import_subscription_options',
        [
            'form_id'      => [ __( 'Donation Form ID', 'give' ), __( 'Form ID', 'give' ) ],
            'donor_id'              => [ __( 'Donor ID', 'give' ) ],
            'first_name'            => [ __( 'Donor First Name', 'give' ), __( 'First Name', 'give' ) ],
            'last_name'             => [ __( 'Donor Last Name', 'give' ), __( 'Last Name', 'give' ) ],
            'email'                 => [ __( 'Donor Email', 'give' ), __( 'Email', 'give' ) ],
            'period'                => [ __( 'Period', 'give' ), __( 'Subscription Period', 'give' ) ],
            'frequency'             => [ __( 'Frequency', 'give' ) ],
            'installments'          => [ __( 'Installments', 'give' ) ],
            'amount'                => [ __( 'Amount', 'give' ), __( 'Recurring Amount', 'give' ) ],
            'fee_amount_recovered'  => [ __( 'Recovered Fee Amount', 'give' ) ],
            'status'                => [ __( 'Status', 'give' ) ],
            'mode'                  => [ __( 'Mode', 'give' ), __( 'Payment Mode', 'give' ) ],
            'transaction_id'        => [ __( 'Transaction ID', 'give' ) ],
            'gateway_id'            => [ __( 'Gateway ID', 'give' ), __( 'Gateway', 'give' ) ],
            'gateway_subscription_id' => [ __( 'Gateway Subscription ID', 'give' ) ],
            'created_at'            => [ __( 'Created At', 'give' ), __( 'Start Date', 'give' ) ],
            'renews_at'             => [ __( 'Renews At', 'give' ), __( 'Next Renewal Date', 'give' ) ],
            'currency'              => [ __( 'Currency', 'give' ) ],
        ]
    );
}

/**
 * Import CSV in DB
 *
 * @param int    $file_id   CSV id.
 * @param int    $start     Start from which csv line.
 * @param int    $end       End from which csv line.
 * @param string $delimiter CSV delimeter.
 *
 * @return array
 */
function give_get_donation_data_from_csv( $file_id, $start, $end, $delimiter = 'csv' ) {
	/**
	 * Filter to modify delimiter of Import
	 *
	 * @since 1.8.14
	 *
	 * @param string $delimiter
	 *
	 * @return string $delimiter
	 */
	$delimiter = (string) apply_filters( 'give_import_delimiter_set', $delimiter );

	$file_dir = give_get_file_data_by_file_id( $file_id );

	return give_get_raw_data_from_file( $file_dir, $start, $end, $delimiter );
}

/**
 * Get raw data from file data
 *
 * @since 2.1
 *
 * @param $file_dir
 * @param $start
 * @param $end
 * @param $delimiter
 *
 * @return array
 */
function give_get_raw_data_from_file( $file_dir, $start, $end, $delimiter ) {
	$raw_data = [];

	$count = 0;
	if ( false !== ( $handle = fopen( $file_dir, 'r' ) ) ) {
		while ( false !== ( $row = fgetcsv( $handle, 0, $delimiter ) ) ) {
			if ( $count >= $start && $count <= $end ) {
				$raw_data[] = $row;
			}
			$count ++;
		}
		fclose( $handle );
	}

	return $raw_data;
}

/**
 * Get content from the attachment id of CSV
 *
 * @since 2.1
 *
 * @param $file_id
 *
 * @return false|string file content
 */
function give_get_file_data_by_file_id( $file_id ) {
	return get_attached_file( $file_id );
}

/**
 * Import CSV (subscriptions) in memory
 *
 * @param int    $file_id
 * @param int    $start
 * @param int    $end
 * @param string $delimiter
 *
 * @return array
 * @unreleased
 */
function give_get_subscription_data_from_csv( $file_id, $start, $end, $delimiter = 'csv' ) {
    $delimiter = (string) apply_filters( 'give_import_delimiter_set', $delimiter );
    $file_dir = give_get_file_data_by_file_id( $file_id );
    return give_get_raw_data_from_file( $file_dir, $start, $end, $delimiter );
}


/**
 * Remove login when user register with give functions.
 *
 * @since 1.8.13
 *
 * @param $value
 *
 * @return bool
 */
function give_log_user_in_on_register_callback( $value ) {
	return false;
}

/**
 * Add import Donation forms, donations , donor from CSV to database
 *
 * @since 1.8.13
 *
 * @param array $raw_key Setup bu user at step 2.
 * @param array $row_data Feilds that are being imported from CSV
 * @param array $main_key First row from the CSV
 * @param array $import_setting Contain the global variable.
 *
 * @return bool
 */
function give_save_import_donation_to_db( $raw_key, $row_data, $main_key = [], $import_setting = [] ) {
	$data                          = array_combine( $raw_key, $row_data );
	$price_id                      = false;
	$donor_id                      = 0;
	$donor_data                    = [];
	$form                          = [];
	$import_setting['create_user'] = isset( $import_setting['create_user'] ) ? $import_setting['create_user'] : 1;
	$dry_run                       = isset( $import_setting['dry_run'] ) ? $import_setting['dry_run'] : false;
	$_dry_run_is_duplicate         = false;
	$dry_run_duplicate_form        = false;
	$dry_run_duplicate_donor       = false;
	$donation_key                  = empty( $import_setting['donation_key'] ) ? 1 : (int) $import_setting['donation_key'];
	$payment_id                    = false;

	$data = (array) apply_filters( 'give_save_import_donation_to_db', $data );

	$data['amount'] = give_maybe_sanitize_amount( $data['amount'] );
	$diff           = [];

	if ( ! empty( $dry_run ) && 1 !== $donation_key ) {
		$csv_raw_data = empty( $import_setting['csv_raw_data'] ) ? [] : $import_setting['csv_raw_data'];
		$donors_list  = empty( $import_setting['donors_list'] ) ? [] : $import_setting['donors_list'];
		$key          = $donation_key - 1;
		for ( $i = 0; $i < $key; $i ++ ) {
			$csv_data           = array_combine( $raw_key, $csv_raw_data[ $i ] );
			$csv_data['amount'] = give_maybe_sanitize_amount( $csv_data['amount'] );
			// check for duplicate donations
			$diff = array_diff( $csv_data, $data );
			if ( empty( $diff ) ) {
				$_dry_run_is_duplicate   = true;
				$dry_run_duplicate_form  = true;
				$dry_run_duplicate_donor = true;
			} else {
				// check for duplicate donation form with form id
				if ( ! empty( $csv_data['form_id'] ) && ! empty( $data['form_id'] ) && $csv_data['form_id'] === $data['form_id'] ) {
					$form    = new Give_Donate_Form( $data['form_id'] );
					$form_id = $form->get_ID();
					if ( ! empty( $form_id ) ) {
						$dry_run_duplicate_form = true;
					}
				}
				// check for duplicate donation form with form title
				if ( empty( $dry_run_duplicate_form ) && ! empty( $csv_data['form_title'] ) && ! empty( $data['form_title'] ) && $csv_data['form_title'] === $data['form_title'] ) {
					$dry_run_duplicate_form = true;
				}

				// check for duplicate donor by donor id
				if ( ! empty( $csv_data['donor_id'] ) && ! empty( $data['donor_id'] ) && $csv_data['donor_id'] === $data['donor_id'] ) {
					$donor = array_search( (int) $data['donor_id'], array_column( $donors_list, 'id' ) );
					if ( ! empty( $donor ) ) {
						$dry_run_duplicate_donor = true;
					}
				}

				// check for duplicate donor by user id
				if ( empty( $dry_run_duplicate_donor ) && ! empty( $csv_data['user_id'] ) && ! empty( $data['user_id'] ) && $csv_data['user_id'] === $data['user_id'] ) {
					$donor = array_search( (int) $data['user_id'], array_column( $donors_list, 'user_id' ) );
					if ( ! empty( $donor ) ) {
						$dry_run_duplicate_donor = true;
					} else {
						$donor = get_user_by( 'id', $csv_data['user_id'] );
						if ( ! empty( $donor->ID ) ) {
							$dry_run_duplicate_donor = true;
						}
					}
				}

				// check for duplicate donor by donor id
				if ( empty( $dry_run_duplicate_donor ) && ! empty( $csv_data['email'] ) && ! empty( $data['email'] ) && $csv_data['email'] === $data['email'] ) {
					$dry_run_duplicate_donor = true;
				}
			}
		}
	}

	if ( empty( $dry_run_duplicate_donor ) ) {
		// Here come the login function.
		$donor_data = give_import_get_user_from_csv( $data, $import_setting );
		if ( empty( $dry_run ) ) {
			if ( ! empty( $donor_data->id ) ) {
				$donor_id = $donor_data->id;
			} else {
				return $payment_id;
			}
		}
	} else {
		// Get the report
		$report                    = give_import_donation_report();
		$report['duplicate_donor'] = ( ! empty( $report['duplicate_donor'] ) ? ( absint( $report['duplicate_donor'] ) + 1 ) : 1 );
		// update the report
		give_import_donation_report_update( $report );
	}

	if ( empty( $dry_run_duplicate_form ) ) {
		// get form data or register a form data.
		$form = give_import_get_form_data_from_csv( $data, $import_setting );
		if ( false == $form && empty( $dry_run ) ) {
			return $payment_id;
		} else {
			$price_id = ( ! empty( $form->price_id ) ) ? $form->price_id : false;
		}
	} else {
		// Get the report
		$report                   = give_import_donation_report();
		$report['duplicate_form'] = ( ! empty( $report['duplicate_form'] ) ? ( absint( $report['duplicate_form'] ) + 1 ) : 1 );
		// update the report
		give_import_donation_report_update( $report );
	}

	// Get the report
	$report = give_import_donation_report();

	$status  = give_import_donation_get_status( $data );
	$country = ( ! empty( $data['country'] ) ? ( ( $country_code = array_search( $data['country'], give_get_country_list() ) ) ? $country_code : $data['country'] ) : '' );
	$state   = ( ! empty( $data['state'] ) ? ( ( $state_code = array_search( $data['state'], give_get_states( $country ) ) ) ? $state_code : $data['state'] ) : '' );

	$address = [
		'line1'   => ( ! empty( $data['line1'] ) ? give_clean( $data['line1'] ) : '' ),
		'line2'   => ( ! empty( $data['line2'] ) ? give_clean( $data['line2'] ) : '' ),
		'city'    => ( ! empty( $data['city'] ) ? give_clean( $data['city'] ) : '' ),
		'zip'     => ( ! empty( $data['zip'] ) ? give_clean( $data['zip'] ) : '' ),
		'state'   => $state,
		'country' => $country,
	];

	$test_mode = [ 'test', 'true' ];
	$post_date = current_time( 'mysql' );
	if ( ! empty( $data['post_date'] ) ) {
		if ( ! empty( $data['post_time'] ) ) {
			$post_date = mysql2date( 'Y-m-d', $data['post_date'] );
			$post_date = mysql2date( 'Y-m-d H:i:s', $post_date . ' ' . $data['post_time'] );
		} else {
			$post_date = mysql2date( 'Y-m-d H:i:s', $data['post_date'] );
		}
	}

	// Create payment_data array
	$payment_data = [
		'donor_id'        => $donor_id,
		'price'           => $data['amount'],
		'status'          => $status,
		'currency'        => ! empty( $data['currency'] ) && array_key_exists( $data['currency'], give_get_currencies_list() ) ? $data['currency'] : give_get_currency(),
		'user_info'       => [
			'id'         => $donor_id,
			'email'      => ( ! empty( $data['email'] ) ? $data['email'] : ( isset( $donor_data->email ) ? $donor_data->email : false ) ),
			'first_name' => ( ! empty( $data['first_name'] ) ? $data['first_name'] : ( ! empty( $donor_id ) && ( $first_name = get_user_meta( $donor_id, 'first_name', true ) ) ? $first_name : $donor_data->name ) ),
			'last_name'  => ( ! empty( $data['last_name'] ) ? $data['last_name'] : ( ! empty( $donor_id ) && ( $last_name = get_user_meta( $donor_id, 'last_name', true ) ) ? $last_name : $donor_data->name ) ),
			'address'    => $address,
			'title'      => ! empty( $data['title_prefix'] ) ? $data['title_prefix'] : '',
		],
		'gateway'         => ( ! empty( $data['gateway'] ) ? strtolower( $data['gateway'] ) : 'manual' ),
		'give_form_title' => ( ! empty( $data['form_title'] ) ? $data['form_title'] : ( method_exists( $form, 'get_name' ) ? $form->get_name() : '' ) ),
		'give_form_id'    => ( ! empty( $form ) && method_exists( $form, 'get_ID' ) ) ? $form->get_ID() : '',
		'give_price_id'   => $price_id,
		'purchase_key'    => strtolower( md5( uniqid() ) ),
		'user_email'      => $data['email'],
		'post_date'       => $post_date,
		'mode'            => ( ! empty( $data['mode'] ) ? ( in_array( strtolower( $data['mode'] ), $test_mode ) ? 'test' : 'live' ) : ( isset( $import_setting['mode'] ) ? ( true == (bool) $import_setting['mode'] ? 'test' : 'live' ) : ( give_is_test_mode() ? 'test' : 'live' ) ) ),
	];

	/**
	 * Filter to modify payment Data before getting imported.
	 *
	 * @since 4.5.0 Add gateway transaction id to payment data
	 * @since 2.1.0
	 *
	 * @param array $payment_data payment data
	 * @param array $payment_data donation data
	 * @param array $donor_data donor data
	 * @param object $donor_data form object
	 *
	 * @return array $payment_data payment data
	 */
	$payment_data = apply_filters( 'give_import_before_import_payment', $payment_data, $data, $donor_data, $form );

	// Get the report
	$report = give_import_donation_report();

	// Check for duplicate code.
	$donation_duplicate = give_check_import_donation_duplicate( $payment_data, $data, $form, $donor_data );
	if ( false !== $donation_duplicate || ! empty( $_dry_run_is_duplicate ) ) {
		$report['donation_details'][ $import_setting['donation_key'] ]['duplicate'] = $donation_duplicate;
		$report['duplicate_donation'] = ( ! empty( $report['duplicate_donation'] ) ? ( absint( $report['duplicate_donation'] ) + 1 ) : 1 );
	} else {

		if ( empty( $dry_run ) ) {
			add_action( 'give_update_payment_status', 'give_donation_import_insert_default_payment_note', 1, 1 );
			add_filter( 'give_insert_payment_args', 'give_donation_import_give_insert_payment_args', 11, 2 );
			add_filter( 'give_update_donor_information', 'give_donation_import_update_donor_information', 11, 3 );
			add_action( 'give_insert_payment', 'give_import_donation_insert_payment', 11, 2 );
			add_filter( 'give_is_stop_email_notification', '__return_true' );

			// If status is other than pending then first change the donation status to pending and after adding the payment meta update the donation status.
			if ( 'pending' !== $status ) {
				unset( $payment_data['status'] );
			}

			$payment_id = give_insert_payment( $payment_data );
			remove_action( 'give_update_payment_status', 'give_donation_import_insert_default_payment_note', 1 );
			remove_filter( 'give_insert_payment_args', 'give_donation_import_give_insert_payment_args', 11 );
			remove_filter( 'give_update_donor_information', 'give_donation_import_update_donor_information', 11 );
			remove_action( 'give_insert_payment', 'give_import_donation_insert_payment', 11 );
			remove_filter( 'give_is_stop_email_notification', '__return_true' );

			if ( $payment_id ) {

				$payment = new Give_Payment( $payment_id );

				$report['create_donation'] = ( ! empty( $report['create_donation'] ) ? ( absint( $report['create_donation'] ) + 1 ) : 1 );

				$payment->update_meta( '_give_payment_import', true );

				if ( ! empty( $import_setting['csv'] ) ) {
					$payment->update_meta( '_give_payment_import_id', $import_setting['csv'] );
				}

				// Insert Company Name.
				if ( ! empty( $data['company_name'] ) ) {
					$payment->update_meta( '_give_donation_company', $data['company_name'] );
					$donor_data->update_meta( '_give_donor_company', $data['company_name'] );
				}

				// Insert Donor IP address.
				if ( ! empty( $data['donor_ip'] ) ) {
					$payment->update_meta( '_give_payment_donor_ip', $data['donor_ip'] );
				}

				// Insert Transaction ID.
				if ( ! empty( $data['gateway_transaction_id'] ) ) {
					give_set_payment_transaction_id( $payment_id, $data['gateway_transaction_id'] );
				}

				// Insert Notes.
				if ( ! empty( $data['notes'] ) ) {
					$payment->add_note( $data['notes'] );
				}

				$meta_exists = array_keys( $raw_key, 'post_meta' );
				if ( ! empty( $main_key ) && ! empty( $meta_exists ) ) {
					foreach ( $meta_exists as $meta_exist ) {
						if ( ! empty( $main_key[ $meta_exist ] ) && ! empty( $row_data[ $meta_exist ] ) ) {
							$payment->update_meta( $main_key[ $meta_exist ], $row_data[ $meta_exist ] );
						}
					}
				}

				// update the donation status if it's other then pending
				if ( 'pending' !== $status ) {
					$payment->update_status( $status );
				}
			} else {
				$report['failed_donation'] = ( ! empty( $report['failed_donation'] ) ? ( absint( $report['failed_donation'] ) + 1 ) : 1 );
				$payment_id                = false;
			}

			/**
			 * Fire after payment is imported and payment meta is also being imported.
			 *
			 * @since 2.1.0
			 *
			 * @param int $payment_id payment id
			 * @param array $payment_data payment data
			 * @param array $payment_data donation data
			 * @param array $donor_data donor data
			 * @param object $donor_data form object
			 */
			do_action( 'give_import_after_import_payment', $payment_id, $payment_data, $data, $donor_data, $form );
		} else {
			$report['create_donation'] = ( ! empty( $report['create_donation'] ) ? ( absint( $report['create_donation'] ) + 1 ) : 1 );
			$payment_id                = true;
		}
	}

	// update the report
	give_import_donation_report_update( $report );

	return $payment_id;
}

/**
 * Add import Subscriptions from CSV to database using Subscription model
 *
 * @param array $raw_key Setup by user at step 2 (mapped property keys)
 * @param array $row_data Row values
 * @param array $main_key First row from the CSV
 * @param array $import_setting Global settings
 *
 * @return bool|int Subscription id or true on dry-run; false on failure
 * @unreleased
 */
function give_save_import_subscription_to_db( $raw_key, $row_data, $main_key = [], $import_setting = [] ) {
    $report = give_import_subscription_report();
    $dry_run = isset( $import_setting['dry_run'] ) ? (bool) $import_setting['dry_run'] : false;

    // Guard: skip empty rows
    if ( empty( $row_data ) || ( is_array( $row_data ) && 0 === count( array_filter( $row_data, function( $v ) { return $v !== null && $v !== '';} ) ) ) ) {
        return true;
    }

    // Guard: ensure column count matches header mapping
    if ( ! is_array( $row_data ) || count( $row_data ) !== count( $raw_key ) ) {
        $report['failed_subscription'] = ( ! empty( $report['failed_subscription'] ) ? ( absint( $report['failed_subscription'] ) + 1 ) : 1 );
        give_import_subscription_report_update( $report );
        return false;
    }

    // Combine keys → values
    $data = array_combine( $raw_key, $row_data );

    // Required fields (donor is donor_id OR email)
    $required = [ 'form_id', 'period', 'frequency', 'amount', 'status' ];
    foreach ( $required as $key ) {
        if ( empty( $data[ $key ] ) && '0' !== (string) ( $data[ $key ] ?? '' ) ) {
            $report['failed_subscription'] = ( ! empty( $report['failed_subscription'] ) ? ( absint( $report['failed_subscription'] ) + 1 ) : 1 );
            $report['errors'][] = sprintf( __( 'Row %1$d: Missing required field "%2$s"', 'give' ), (int) ( $import_setting['row_key'] ?? 0 ), $key );
            give_import_subscription_report_update( $report );
            return 'Missing required field ' . $key;
        }
    }
    if ( empty( $data['donor_id'] ) && empty( $data['email'] ) ) {
        $report['failed_subscription'] = ( ! empty( $report['failed_subscription'] ) ? ( absint( $report['failed_subscription'] ) + 1 ) : 1 );
        $report['errors'][] = sprintf( __( 'Row %d: Either donor_id or email is required to resolve the donor', 'give' ), (int) ( $import_setting['row_key'] ?? 0 ) );
        give_import_subscription_report_update( $report );
        return 'Missing donor identifier (donor_id or email)';
    }

    // Build attributes for Subscription model
    try {
        $currency = ! empty( $data['currency'] ) && array_key_exists( $data['currency'], give_get_currencies_list() ) ? $data['currency'] : give_get_currency();

        $attributes = [];
        $attributes['donationFormId'] = (int) $data['form_id'];

        // Resolve donor id
        $resolvedDonorId = 0;
        if ( ! empty( $data['donor_id'] ) ) {
            $resolvedDonorId = (int) $data['donor_id'];
        } else {
            // Resolve via Donor model action
            try {
                $email = (string) $data['email'];
                $firstNameCsv = (string) ( $data['first_name'] ?? '' );
                $lastNameCsv  = (string) ( $data['last_name'] ?? '' );
                $donorModel = give( \Give\DonationForms\Actions\GetOrCreateDonor::class )( null, $email, $firstNameCsv, $lastNameCsv, null, null );
                // Optionally create a WP user for the donor if requested
                if ( ! empty( $import_setting['create_user'] ) && (int) $import_setting['create_user'] === 1 ) {
                    try {
                        $donorModel = give( \Give\Donors\Actions\CreateUserFromDonor::class )( $donorModel );
                    } catch ( \Throwable $e ) {
                        // ignore user creation failure, continue with donor as-is
                    }
                }
                $resolvedDonorId = (int) $donorModel->id;
            } catch ( \Throwable $e ) {
                $report['failed_subscription'] = ( ! empty( $report['failed_subscription'] ) ? ( absint( $report['failed_subscription'] ) + 1 ) : 1 );
                give_import_subscription_report_update( $report );
                return false;
            }
        }
        $attributes['donorId'] = $resolvedDonorId;
		// Normalize and validate subscription period from raw input
		$rawPeriod = strtolower( trim( (string) $data['period'] ) );
		$periodAliases = [
			'daily'    => 'day',
			'days'     => 'day',
			'day'      => 'day',
			'weekly'   => 'week',
			'weeks'    => 'week',
			'week'     => 'week',
			'monthly'  => 'month',
			'months'   => 'month',
			'month'    => 'month',
			'quarterly'=> 'quarter',
			'quarters' => 'quarter',
			'qtr'      => 'quarter',
			'qtrs'     => 'quarter',
			'quarter'  => 'quarter',
			'yearly'   => 'year',
			'annually' => 'year',
			'annual'   => 'year',
			'yrs'      => 'year',
			'yr'       => 'year',
			'years'    => 'year',
			'year'     => 'year',
		];
		$normalizedPeriod = isset( $periodAliases[ $rawPeriod ] ) ? $periodAliases[ $rawPeriod ] : $rawPeriod;
		if ( ! \Give\Subscriptions\ValueObjects\SubscriptionPeriod::isValid( $normalizedPeriod ) ) {
			throw new \UnexpectedValueException( sprintf(
				__( 'Invalid subscription period "%1$s". Valid options: %2$s. You can also use: daily, weekly, monthly, quarterly, yearly.', 'give' ),
				(string) $data['period'],
				implode( ', ', array_values( \Give\Subscriptions\ValueObjects\SubscriptionPeriod::toArray() ) )
			) );
		}
		$attributes['period'] = new \Give\Subscriptions\ValueObjects\SubscriptionPeriod( $normalizedPeriod );
        $attributes['frequency'] = (int) $data['frequency'];
        $attributes['installments'] = isset( $data['installments'] ) ? (int) $data['installments'] : 0;
        $attributes['transactionId'] = isset( $data['transaction_id'] ) ? (string) $data['transaction_id'] : '';

        // Mode
        if ( ! empty( $data['mode'] ) ) {
            $mode = strtolower( (string) $data['mode'] );
        } else {
            $mode = ( isset( $import_setting['mode'] ) && $import_setting['mode'] ) ? 'test' : ( give_is_test_mode() ? 'test' : 'live' );
        }
        $attributes['mode'] = new \Give\Subscriptions\ValueObjects\SubscriptionMode( $mode );

        // Amounts
        $amountDecimal = is_string( $data['amount'] ) ? preg_replace( '/[\$,]/', '', $data['amount'] ) : $data['amount'];
        $attributes['amount'] = \Give\Framework\Support\ValueObjects\Money::fromDecimal( $amountDecimal, $currency );

        if ( isset( $data['fee_amount_recovered'] ) && $data['fee_amount_recovered'] !== '' ) {
            $feeDecimal = is_string( $data['fee_amount_recovered'] ) ? preg_replace( '/[\$,]/', '', $data['fee_amount_recovered'] ) : $data['fee_amount_recovered'];
            $attributes['feeAmountRecovered'] = \Give\Framework\Support\ValueObjects\Money::fromDecimal( $feeDecimal, $currency );
        }

        // Status
        $attributes['status'] = new \Give\Subscriptions\ValueObjects\SubscriptionStatus( strtolower( trim( (string) $data['status'] ) ) );

        if ( ! empty( $data['gateway_id'] ) ) {
            $attributes['gatewayId'] = (string) $data['gateway_id'];
        }
        if ( ! empty( $data['gateway_subscription_id'] ) ) {
            $attributes['gatewaySubscriptionId'] = (string) $data['gateway_subscription_id'];
        }

        // Dates
        if ( ! empty( $data['created_at'] ) ) {
            $attributes['createdAt'] = new \DateTime( (string) $data['created_at'] );
        }
        if ( ! empty( $data['renews_at'] ) ) {
            $attributes['renewsAt'] = new \DateTime( (string) $data['renews_at'] );
        }

        if ( $dry_run ) {
            $report['create_subscription'] = ( ! empty( $report['create_subscription'] ) ? ( absint( $report['create_subscription'] ) + 1 ) : 1 );
            give_import_subscription_report_update( $report );
            return true;
        }

        $subscription = \Give\Subscriptions\Models\Subscription::create( $attributes );

        if ( $subscription && $subscription->id ) {
            // Create initial donation for the subscription
            try {
                // Infer donor identity for required Donation model fields
                $donorModel = null;
                try {
                    $donorModel = \Give\Donors\Models\Donor::find($subscription->donorId);
                } catch (\Throwable $e) {
                    $donorModel = null;
                }

                $donorEmail = $donorModel && isset($donorModel->email) ? (string) $donorModel->email : '';
                $donorName  = $donorModel && isset($donorModel->name) ? (string) $donorModel->name : '';
                $firstName  = '';
                $lastName   = '';
                if ($donorName) {
                    $parts = preg_split('/\s+/', trim($donorName));
                    if ($parts) {
                        $firstName = (string) array_shift($parts);
                        $lastName  = (string) trim(implode(' ', $parts));
                    }
                }

                $donationAttributes = [
                    'subscriptionId' => $subscription->id,
                    'gatewayId' => ! empty( $attributes['gatewayId'] ) ? $attributes['gatewayId'] : 'manual',
                    'amount' => $subscription->amount,
                    'status' => \Give\Donations\ValueObjects\DonationStatus::COMPLETE(),
                    'type' => \Give\Donations\ValueObjects\DonationType::SUBSCRIPTION(),
                    'donorId' => $subscription->donorId,
                    'formId' => $subscription->donationFormId,
                    'feeAmountRecovered' => $subscription->feeAmountRecovered,
                    'mode' => $subscription->mode->isLive() ? \Give\Donations\ValueObjects\DonationMode::LIVE() : \Give\Donations\ValueObjects\DonationMode::TEST(),
                    'firstName' => $firstName,
                    'lastName'  => $lastName,
                    'email'     => $donorEmail,
                ];

                // If CSV provided identity fields alongside donor_id, prefer those for the initial donation
                if ( ! empty( $data['first_name'] ) ) {
                    $donationAttributes['firstName'] = (string) $data['first_name'];
                }
                if ( ! empty( $data['last_name'] ) ) {
                    $donationAttributes['lastName'] = (string) $data['last_name'];
                }
                if ( ! empty( $data['email'] ) ) {
                    $donationAttributes['email'] = (string) $data['email'];
                }

                if ( ! empty( $attributes['transactionId'] ) ) {
                    $donationAttributes['gatewayTransactionId'] = (string) $attributes['transactionId'];
                }
                if ( ! empty( $subscription->createdAt ) ) {
                    $donationAttributes['createdAt'] = $subscription->createdAt;
                }

                $initialDonation = \Give\Donations\Models\Donation::create( $donationAttributes );

                // Maintain legacy linkage and set payment_mode in subscriptions table
                if ( $initialDonation && $initialDonation->id ) {
                    give()->subscriptions->updateLegacyParentPaymentId( $subscription->id, $initialDonation->id );
                    // Backwards compatibility updates (donor totals, fee meta)
                    if ( function_exists('give_import_update_legacy_after_initial_donation') ) {
                        give_import_update_legacy_after_initial_donation( $initialDonation );
                    }
                }
            } catch ( \Throwable $e ) {
                // If initial donation fails, still report subscription created but count a failure for visibility
                $report['failed_subscription_initial_donation'] = ( ! empty( $report['failed_subscription_initial_donation'] ) ? ( absint( $report['failed_subscription_initial_donation'] ) + 1 ) : 1 );
                $report['errors'][] = sprintf( __( 'Row %1$d: Initial donation creation failed (%2$s)', 'give' ), (int) ( $import_setting['row_key'] ?? 0 ), $e->getMessage() );
            }
            $report['create_subscription'] = ( ! empty( $report['create_subscription'] ) ? ( absint( $report['create_subscription'] ) + 1 ) : 1 );
            give_import_subscription_report_update( $report );
            return (int) $subscription->id;
        }

        $report['failed_subscription'] = ( ! empty( $report['failed_subscription'] ) ? ( absint( $report['failed_subscription'] ) + 1 ) : 1 );
        give_import_subscription_report_update( $report );
        return false;

    } catch ( \Throwable $e ) {
        $report['failed_subscription'] = ( ! empty( $report['failed_subscription'] ) ? ( absint( $report['failed_subscription'] ) + 1 ) : 1 );
        $report['errors'][] = sprintf( __( 'Row %1$d: %2$s', 'give' ), (int) ( $import_setting['row_key'] ?? 0 ), $e->getMessage() );
        give_import_subscription_report_update( $report );
        return $e->getMessage();
    }
}

/**
 * Get Donation form status
 *
 * @since 2.0.2
 *
 * @param array $data donation data that is goingt o get imported
 *
 * @return string $status Donation status.
 */
function give_import_donation_get_status( $data ) {
	if ( empty( $data['post_status'] ) ) {
		return 'publish';
	}

	$status = 'publish';

	$donation_status     = trim( $data['post_status'] );
	$donation_status_key = strtolower( preg_replace( '/\s+/', '', $donation_status ) );

	foreach ( give_get_payment_statuses() as $key => $value ) {
		$match = false;
		if ( $key === $donation_status_key ) {
			$match = true;
		} elseif ( stristr( $donation_status, $value ) ) {
			$match = true;
		}

		if ( ! empty( $match ) ) {
			$status = $key;
			break;
		}
	}

	return $status;
}

/**
 * Alter donor information when importing donations from CSV
 *
 * @since 1.8.13
 *
 * @param $donor
 * @param $payment_id
 * @param $payment_data
 *
 * @return Give_Donor
 */
function give_donation_import_update_donor_information( $donor, $payment_id, $payment_data ) {
	$old_donor = $donor;
	if ( ! empty( $payment_data['donor_id'] ) ) {
		$donor_id = absint( $payment_data['donor_id'] );
		$donor    = new Give_Donor( $donor_id );
		if ( ! empty( $donor->id ) ) {
			return $donor;
		}
	}

	return $old_donor;
}

/*
 * Give update purchase_count of give customer.
 *
 * @since 1.8.13
 */
function give_import_donation_insert_payment( $payment_id, $payment_data ) {
	// Update Give Customers purchase_count
	if ( ! empty( $payment_data['status'] ) && ( 'complete' === (string) $payment_data['status'] || 'publish' === (string) $payment_data['status'] ) ) {
		$donor_id = (int) get_post_meta( $payment_id, '_give_payment_customer_id', true );
		if ( ! empty( $donor_id ) ) {
			$donor = new Give_Donor( $donor_id );
			$donor->increase_purchase_count();
		}
	}
}

/**
 * Add author id in in donation post
 *
 * @since 1.8.13
 */
function give_donation_import_give_insert_payment_args( $args, $payment_data ) {
	if ( ! empty( $payment_data['user_info']['id'] ) ) {
		$args['post_author'] = (int) $payment_data['user_info']['id'];
	}

	return $args;
}

/**
 * Check if Import donation is duplicate
 *
 * @since 1.8.13
 */
function give_check_import_donation_duplicate( $payment_data, $data, $form, $donor_data ) {
	$return = false;
	if ( ! empty( $data['post_date'] ) ) {
		$post_date = mysql2date( 'Y-m-d-H-i-s', $payment_data['post_date'] );
		$post_date = explode( '-', $post_date );
		$args      = [
			'output'                 => 'post',
			'cache_results'          => false,
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'fields'                 => 'ids',
			'date_query'             => [
				[
					'year'   => $post_date[0],
					'month'  => $post_date[1],
					'day'    => $post_date[2],
					'hour'   => $post_date[3],
					'minute' => $post_date[4],
					'second' => $post_date[5],
				],
			],
			'meta_query'             => [
				[
					'key'     => '_give_payment_total',
					'value'   => preg_replace( '/[\$,]/', '', $payment_data['price'] ),
					'compare' => 'LIKE',
				],
				[
					'key'     => '_give_payment_form_id',
					'value'   => $payment_data['give_form_id'],
					'type'    => 'numeric',
					'compare' => '=',
				],
				[
					'key'     => '_give_payment_gateway',
					'value'   => $payment_data['gateway'],
					'compare' => '=',
				],
				[
					'key'     => '_give_payment_donor_id',
					'value'   => isset( $donor_data->id ) ? $donor_data->id : '',
					'compare' => '=',
				],
				[
					'key'     => '_give_payment_mode',
					'value'   => $payment_data['mode'],
					'compare' => '=',
				],
			],
		];

		$payments  = new Give_Payments_Query( $args );
		$donations = $payments->get_payments();
		if ( ! empty( $donations ) ) {
			$return = $donations;
		}
	}

	/**
	 * Filter to modify donation which is getting add is duplicate or not.
	 *
	 * @since 1.8.18
	 */
	return apply_filters( 'give_check_import_donation_duplicate', $return, $payment_data, $data, $form, $donor_data );
}

/**
 * Record payment notes that is being imported from CSV.
 *
 * @since  1.8.13
 *
 * @param  int $payment_id The ID number of the payment.
 *
 * @return void
 */
function give_donation_import_insert_default_payment_note( $payment_id ) {
	$current_user = wp_get_current_user();
	give_insert_payment_note( $payment_id, wp_sprintf( __( 'This donation was imported by %s', 'give' ), $current_user->user_email ) );
}

/**
 * Return Import Page URL
 *
 * @since 1.8.13
 *
 * @param array $parameter
 *
 * @return string URL
 */
function give_import_page_url( $parameter = [] ) {
	$defalut_query_arg = [
		'post_type'     => 'give_forms',
		'page'          => 'give-tools',
		'tab'           => 'import',
		'importer-type' => 'import_donations',
	];
	$import_query_arg  = wp_parse_args( $parameter, $defalut_query_arg );

	return esc_url_raw( add_query_arg( $import_query_arg, admin_url( 'edit.php' ) ) );
}

/**
 * Update legacy donor totals and fee meta for a newly created initial donation
 *
 * This mirrors the logic used elsewhere to keep backwards compatibility fields in sync.
 *
 * @unreleased
 */
function give_import_update_legacy_after_initial_donation( \Give\Donations\Models\Donation $donation ) {
    try {
        $donor = $donation->donor;

        if ( $donor && isset( $donor->id ) ) {
            // Update legacy donor purchase totals and counts
            give()->donors->updateLegacyColumns(
                $donor->id,
                [
                    'purchase_value' => give_import_get_donor_total_intended_amount( (int) $donor->id ),
                    'purchase_count' => $donor->totalDonations(),
                ]
            );
        }

        // Store fee-recovery intended amount meta on donation, if applicable
        if ( null !== $donation->feeAmountRecovered ) {
            give()->payment_meta->update_meta(
                $donation->id,
                '_give_fee_donation_amount',
                give_sanitize_amount_for_db(
                    $donation->intendedAmount()->formatToDecimal(),
                    [ 'currency' => $donation->amount->getCurrency() ]
                )
            );
        }
    } catch ( \Throwable $e ) {
        // silently ignore; non-critical
    }
}

/**
 * Calculate total intended (amount - recovered fee) for a donor across donations
 *
 * @unreleased
 */
function give_import_get_donor_total_intended_amount( int $donorId ): float {
    return (float) DB::table('posts', 'posts')
        ->join(function ($join) {
            $join->leftJoin('give_donationmeta', 'donor_meta')
                ->on('posts.ID', 'donor_meta.donation_id')
                ->andOn('donor_meta.meta_key', DonationMetaKeys::DONOR_ID, true);
        })
        ->join(function ($join) {
            $join->leftJoin('give_donationmeta', 'amount_meta')
                ->on('posts.ID', 'amount_meta.donation_id')
                ->andOn('amount_meta.meta_key', DonationMetaKeys::AMOUNT, true);
        })
        ->join(function ($join) {
            $join->leftJoin('give_donationmeta', 'fee_meta')
                ->on('posts.ID', 'fee_meta.donation_id')
                ->andOn('fee_meta.meta_key', DonationMetaKeys::FEE_AMOUNT_RECOVERED, true);
        })
        ->where('posts.post_type', 'give_payment')
        ->where('donor_meta.meta_value', $donorId)
        ->whereIn('posts.post_status', ['publish', 'give_subscription'])
        ->sum('IFNULL(amount_meta.meta_value, 0) - IFNULL(fee_meta.meta_value, 0)');
}
