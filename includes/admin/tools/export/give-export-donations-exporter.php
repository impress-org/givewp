<?php
/**
 * Payments Export Class.
 *
 * This class handles payment export in batches.
 *
 * @package     Give
 * @subpackage  Admin/Reports
 * @copyright   Copyright (c) 2016, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       2.1
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Give_Export_Donations_CSV Class
 *
 * @since 2.1
 */
class Give_Export_Donations_CSV extends Give_Batch_Export {

	/**
	 * Our export type. Used for export-type specific filters/actions.
	 *
	 * @since 2.1
	 *
	 * @var string
	 */
	public $export_type = 'payments';

	/**
	 * Form submission data.
	 *
	 * @since 2.1
	 *
	 * @var array
	 */
	public $data = array();

	/**
	 * Form submission data.
	 *
	 * @since 2.1
	 *
	 * @var array
	 */
	public $cols = array();

	/**
	 * Form ID.
	 *
	 * @since 2.1
	 *
	 * @var string
	 */
	public $form_id = '';

	/**
	 * Form tags ids.
	 *
	 * @since 2.1
	 *
	 * @var array
	 */
	public $tags = '';


	/**
	 * Form categories ids.
	 *
	 * @since 2.1
	 *
	 * @var array
	 */
	public $categories = '';

	/**
	 * Set the properties specific to the export.
	 *
	 * @since 2.1
	 *
	 * @param array $request The Form Data passed into the batch processing.
	 */
	public function set_properties( $request ) {

		// Set data from form submission
		if ( isset( $_POST['form'] ) ) {
			$this->data = give_clean( wp_parse_args( $_POST['form'] ) );
		}

		$this->form       = $this->data['forms'];
		$this->categories = ! empty( $request['give_forms_categories'] ) ? (array) $request['give_forms_categories'] : array();
		$this->tags       = ! empty( $request['give_forms_tags'] ) ? (array) $request['give_forms_tags'] : array();
		$this->form_id    = $this->get_form_ids( $request );
		$this->price_id   = isset( $request['give_price_option'] ) && ! in_array( $this->price_id, array( 'all', '' ) ) ? absint( $request['give_price_option'] ) : null;
		$this->start      = ! empty( $request['start'] ) ? date( 'Y-m-d', strtotime( $request['start'] ) ) : '';
		$this->end        = ! empty( $request['end'] ) ? date( 'Y-m-d', strtotime( $request['end'] ) ) : '';
		$this->status     = isset( $request['status'] ) ? sanitize_text_field( $request['status'] ) : 'complete';

		/**
		 * Hook to use after setting properties.
		 *
		 * @since 2.1.3
		 */
		do_action( 'give_export_donations_form_data', $this->data );
	}

	/**
	 * Get donation form id list
	 *
	 * @since 2.1
	 *
	 * @param array $request form data that need to be exported
	 *
	 * @return array|boolean|null $form get all the donation id that need to be exported
	 */
	public function get_form_ids( $request = array() ) {
		$form = ! empty( $request['forms'] ) && 0 !== $request['forms'] ? absint( $request['forms'] ) : null;

		$form_ids = ! empty( $request['form_ids'] ) ? sanitize_text_field( $request['form_ids'] ) : null;

		if ( empty( $form ) && ! empty( $form_ids ) && ( ! empty( $this->categories ) || ! empty( $this->tags ) ) ) {
			$form = explode( ',', $form_ids );
		}

		return $form;
	}

	/**
	 * Set the CSV columns.
	 *
	 * @access public
	 *
	 * @since  2.1
	 *
	 * @return array|bool $cols All the columns.
	 */
	public function csv_cols() {

		$columns = isset( $this->data['give_give_donations_export_option'] ) ? $this->data['give_give_donations_export_option'] : array();

		// We need columns.
		if ( empty( $columns ) ) {
			return false;
		}

		$this->cols = $this->get_cols( $columns );

		return $this->cols;
	}


	/**
	 * CSV file columns.
	 *
	 * @since  2.1
	 *
	 * @param array $columns
	 *
	 * @return array
	 */
	private function get_cols( $columns ) {

		$cols = array();

		foreach ( $columns as $key => $value ) {

			switch ( $key ) {
				case 'donation_id':
					$cols['donation_id'] = __( 'Donation ID', 'give' );
					break;
				case 'seq_id':
					$cols['seq_id'] = __( 'Donation Number', 'give' );
					break;
				case 'title_prefix':
					$cols['title_prefix'] = __( 'Title Prefix', 'give' );
					break;
				case 'first_name':
					$cols['first_name'] = __( 'First Name', 'give' );
					break;
				case 'last_name':
					$cols['last_name'] = __( 'Last Name', 'give' );
					break;
				case 'email':
					$cols['email'] = __( 'Email Address', 'give' );
					break;
				case 'company':
					$cols['company'] = __( 'Company Name', 'give' );
					break;
				case 'address':
					$cols['address_line1']   = __( 'Address 1', 'give' );
					$cols['address_line2']   = __( 'Address 2', 'give' );
					$cols['address_city']    = __( 'City', 'give' );
					$cols['address_state']   = __( 'State', 'give' );
					$cols['address_zip']     = __( 'Zip', 'give' );
					$cols['address_country'] = __( 'Country', 'give' );
					break;
				case 'comment':
					$cols['comment'] = __( 'Donor Comment', 'give' );
					break;
				case 'donation_total':
					$cols['donation_total'] = __( 'Donation Total', 'give' );
					break;
				case 'currency_code':
					$cols['currency_code'] = __( 'Currency Code', 'give' );
					break;
				case 'currency_symbol':
					$cols['currency_symbol'] = __( 'Currency Symbol', 'give' );
					break;
				case 'donation_status':
					$cols['donation_status'] = __( 'Donation Status', 'give' );
					break;
				case 'payment_gateway':
					$cols['payment_gateway'] = __( 'Payment Gateway', 'give' );
					break;
				case 'payment_mode':
					$cols['payment_mode'] = __( 'Payment Mode', 'give' );
					break;
				case 'form_id':
					$cols['form_id'] = __( 'Form ID', 'give' );
					break;
				case 'form_title':
					$cols['form_title'] = __( 'Form Title', 'give' );
					break;
				case 'form_level_id':
					$cols['form_level_id'] = __( 'Level ID', 'give' );
					break;
				case 'form_level_title':
					$cols['form_level_title'] = __( 'Level Title', 'give' );
					break;
				case 'donation_date':
					$cols['donation_date'] = __( 'Donation Date', 'give' );
					break;
				case 'donation_time':
					$cols['donation_time'] = __( 'Donation Time', 'give' );
					break;
				case 'userid':
					$cols['userid'] = __( 'User ID', 'give' );
					break;
				case 'donorid':
					$cols['donorid'] = __( 'Donor ID', 'give' );
					break;
				case 'donor_ip':
					$cols['donor_ip'] = __( 'Donor IP Address', 'give' );
					break;
				case 'donation_note_private':
					$cols['donation_note_private'] = __( 'Donation Note (private)', 'give' );
					break;
				case 'donation_note_to_donor':
					$cols['donation_note_to_donor'] = __( 'Donation Note (to donor)', 'give' );
					break;
				default:
					$cols[ $key ] = $key;

			}
		}

		/**
		 * Filter to get columns name when exporting donation
		 *
		 * @since 2.1
		 *
		 * @param array $cols    columns name for CSV
		 * @param array $columns columns select by admin to export
		 */
		return (array) apply_filters( 'give_export_donation_get_columns_name', $cols, $columns );
	}

	/**
	 * Get the donation argument
	 *
	 * @since 2.1
	 *
	 * @param array $args donation argument
	 *
	 * @return array $args donation argument
	 */
	public function get_donation_argument( $args = array() ) {
		$defaults = array(
			'number' => 30,
			'page'   => $this->step,
			'status' => $this->status,
		);
		// Date query.
		if ( ! empty( $this->start ) || ! empty( $this->end ) ) {
			if ( ! empty( $this->start ) ) {
				$defaults['date_query'][0]['after'] = "{$this->start} 00:00:00";
			}
			if ( ! empty( $this->end ) ) {
				$defaults['date_query'][0]['before'] = "{$this->end} 23:59:59";
			}
		}

		if ( ! empty( $this->form_id ) ) {
			$defaults['give_forms'] = is_array( $this->form_id ) ? $this->form_id : array( $this->form_id );
		}

		/**
		 * Filter to modify Payment Query arguments for exporting
		 * donations.
		 *
		 * @since 2.1.3
		 */
		return apply_filters( 'give_export_donations_donation_query_args', wp_parse_args( $args, $defaults ) );
	}

	/**
	 * Get the Export Data.
	 *
	 * @access public
	 *
	 * @since  2.1
	 *
	 * @global object $wpdb Used to query the database using the WordPress database API.
	 *
	 * @return array $data The data for the CSV file.
	 */
	public function get_data() {

		$data = array();
		$i    = 0;
		// Payment query.
		$payments = give_get_payments( $this->get_donation_argument() );

		if ( $payments ) {

			foreach ( $payments as $payment ) {

				$columns      = $this->csv_cols();
				$payment      = new Give_Payment( $payment->ID );
				$payment_meta = $payment->payment_meta;
				$address      = $payment->address;

				// Set columns.
				if ( ! empty( $columns['donation_id'] ) ) {
					$data[ $i ]['donation_id'] = $payment->ID;
				}

				if ( ! empty( $columns['seq_id'] ) ) {
					$data[ $i ]['seq_id'] = Give()->seq_donation_number->get_serial_code( $payment->ID );
				}

				if ( ! empty( $columns['title_prefix'] ) ) {
					$data[ $i ]['title_prefix'] = ! empty( $payment->title_prefix ) ? $payment->title_prefix : '';
				}

				if ( ! empty( $columns['first_name'] ) ) {
					$data[ $i ]['first_name'] = isset( $payment->first_name ) ? $payment->first_name : '';
				}

				if ( ! empty( $columns['last_name'] ) ) {
					$data[ $i ]['last_name'] = isset( $payment->last_name ) ? $payment->last_name : '';
				}

				if ( ! empty( $columns['email'] ) ) {
					$data[ $i ]['email'] = $payment->email;
				}

				if ( ! empty( $columns['company'] ) ) {
					$data[ $i ]['company'] = empty( $payment_meta['_give_donation_company'] ) ? '' : str_replace( "\'", "'", $payment_meta['_give_donation_company'] );
				}

				if ( ! empty( $columns['address_line1'] ) ) {
					$data[ $i ]['address_line1']   = isset( $address['line1'] ) ? $address['line1'] : '';
					$data[ $i ]['address_line2']   = isset( $address['line2'] ) ? $address['line2'] : '';
					$data[ $i ]['address_city']    = isset( $address['city'] ) ? $address['city'] : '';
					$data[ $i ]['address_state']   = isset( $address['state'] ) ? $address['state'] : '';
					$data[ $i ]['address_zip']     = isset( $address['zip'] ) ? $address['zip'] : '';
					$data[ $i ]['address_country'] = isset( $address['country'] ) ? $address['country'] : '';
				}

				if ( ! empty( $columns['comment'] ) ) {
					$comment               = give_get_donor_donation_comment( $payment->ID, $payment->donor_id );
					$data[ $i ]['comment'] = ! empty( $comment ) ? $comment->comment_content : '';
				}

				if ( ! empty( $columns['donation_total'] ) ) {
					$data[ $i ]['donation_total'] = give_format_amount( give_donation_amount( $payment->ID ) );
				}

				if ( ! empty( $columns['currency_code'] ) ) {
					$data[ $i ]['currency_code'] = empty( $payment_meta['_give_payment_currency'] ) ? give_get_currency() : $payment_meta['_give_payment_currency'];
				}

				if ( ! empty( $columns['currency_symbol'] ) ) {
					$currency_code                 = $data[ $i ]['currency_code'];
					$data[ $i ]['currency_symbol'] = give_currency_symbol( $currency_code, true );
				}

				if ( ! empty( $columns['donation_status'] ) ) {
					$data[ $i ]['donation_status'] = give_get_payment_status( $payment, true );
				}

				if ( ! empty( $columns['payment_gateway'] ) ) {
					$data[ $i ]['payment_gateway'] = $payment->gateway;
				}

				if ( ! empty( $columns['payment_mode'] ) ) {
					$data[ $i ]['payment_mode'] = $payment->mode;
				}

				if ( ! empty( $columns['form_id'] ) ) {
					$data[ $i ]['form_id'] = $payment->form_id;
				}

				if ( ! empty( $columns['form_title'] ) ) {
					$data[ $i ]['form_title'] = get_the_title( $payment->form_id );
				}

				if ( ! empty( $columns['form_level_id'] ) ) {
					$data[ $i ]['form_level_id'] = $payment->price_id;
				}

				if ( ! empty( $columns['form_level_title'] ) ) {
					$var_prices = give_has_variable_prices( $payment->form_id );
					if ( empty( $var_prices ) ) {
						$data[ $i ]['form_level_title'] = '';
					} else {
						if ( 'custom' === $payment->price_id ) {
							$custom_amount_text = give_get_meta( $payment->form_id, '_give_custom_amount_text', true );

							if ( empty( $custom_amount_text ) ) {
								$custom_amount_text = esc_html__( 'Custom', 'give' );
							}
							$data[ $i ]['form_level_title'] = $custom_amount_text;
						} else {
							$data[ $i ]['form_level_title'] = give_get_price_option_name( $payment->form_id, $payment->price_id );
						}
					}
				}

				if ( ! empty( $columns['donation_date'] ) ) {
					$payment_date                = strtotime( $payment->date );
					$data[ $i ]['donation_date'] = date( give_date_format(), $payment_date );
				}

				if ( ! empty( $columns['donation_time'] ) ) {
					$payment_date                = strtotime( $payment->date );
					$data[ $i ]['donation_time'] = date_i18n( 'H', $payment_date ) . ':' . date( 'i', $payment_date );
				}

				if ( ! empty( $columns['userid'] ) ) {
					$data[ $i ]['userid'] = $payment->user_id;
				}

				if ( ! empty( $columns['donorid'] ) ) {
					$data[ $i ]['donorid'] = $payment->customer_id;
				}

				if ( ! empty( $columns['donor_ip'] ) ) {
					$data[ $i ]['donor_ip'] = give_get_payment_user_ip( $payment->ID );
				}

				if ( ! empty( $columns['donation_note_private'] ) ) {
					$comments = Give()->comment->db->get_comments(
						array(
							'comment_parent' => $payment->ID,
							'comment_type'   => 'donation',
							'meta_query'     => array(
								'relation' => 'OR',
								array(
									'key'     => 'note_type',
									'compare' => 'NOT EXISTS',
								),
								array(
									'key'     => 'note_type',
									'value'   => 'donor',
									'compare' => '!=',
								),
							),
						)
					);

					$comment_html = array();

					if ( ! empty( $comments ) ) {
						foreach ( $comments as $comment ) {
							$comment_html[] = sprintf(
								'%s - %s',
								date( 'Y-m-d', strtotime( $comment->comment_date ) ),
								$comment->comment_content
							);
						}
					}

					$data[ $i ]['donation_note_private'] = implode( "\n", $comment_html );
				}

				if ( ! empty( $columns['donation_note_to_donor'] ) ) {
					$comments = Give()->comment->db->get_comments(
						array(
							'comment_parent' => $payment->ID,
							'comment_type'   => 'donation',
							'meta_query'     => array(
								array(
									'key'   => 'note_type',
									'value' => 'donor',
								),
							),
						)
					);

					$comment_html = array();

					if ( ! empty( $comments ) ) {
						foreach ( $comments as $comment ) {
							$comment_html[] = sprintf(
								'%s - %s',
								date( 'Y-m-d', strtotime( $comment->comment_date ) ),
								$comment->comment_content
							);
						}
					}
					$data[ $i ]['donation_note_to_donor'] = implode( "\n", $comment_html );
				}

				// Add custom field data.
				// First we remove the standard included keys from above.
				$remove_keys = array(
					'donation_id',
					'seq_id',
					'first_name',
					'last_name',
					'email',
					'address_line1',
					'address_line2',
					'address_city',
					'address_state',
					'address_zip',
					'address_country',
					'donation_total',
					'payment_gateway',
					'payment_mode',
					'form_id',
					'form_title',
					'form_level_id',
					'form_level_title',
					'donation_date',
					'donation_time',
					'userid',
					'donorid',
					'donor_ip',
				);

				// Removing above keys...
				foreach ( $remove_keys as $key ) {
					unset( $columns[ $key ] );
				}

				// Now loop through remaining meta fields.
				foreach ( $columns as $col ) {
					$field_data         = get_post_meta( $payment->ID, $col, true );
					$data[ $i ][ $col ] = $field_data;
					unset( $columns[ $col ] );
				}

				/**
				 * Filter to modify Donation CSV data when exporting donation
				 *
				 * @since 2.1
				 *
				 * @param array Donation data
				 * @param Give_Payment              $payment Instance of Give_Payment
				 * @param array                     $columns Donation data $columns that are not being merge
				 * @param Give_Export_Donations_CSV $this    Instance of Give_Export_Donations_CSV
				 *
				 * @return array Donation data
				 */
				$data[ $i ] = apply_filters( 'give_export_donation_data', $data[ $i ], $payment, $columns, $this );

				$new_data = array();
				$old_data = $data[ $i ];

				// sorting the columns bas on row
				foreach ( $this->csv_cols() as $key => $value ) {
					if ( array_key_exists( $key, $old_data ) ) {
						$new_data[ $key ] = $old_data[ $key ];
					}
				}

				$data[ $i ] = $new_data;

				// Increment iterator.
				$i ++;

			}

			$data = apply_filters( 'give_export_get_data', $data );
			$data = apply_filters( "give_export_get_data_{$this->export_type}", $data );

			return $data;

		}

		return array();

	}

	/**
	 * Return the calculated completion percentage.
	 *
	 * @since 2.1
	 *
	 * @return int
	 */
	public function get_percentage_complete() {
		$args = $this->get_donation_argument(
			array(
				'number' => - 1,
				'output' => '',
			)
		);
		if ( isset( $args['page'] ) ) {
			unset( $args['page'] );
		}
		$query      = give_get_payments( $args );
		$total      = count( $query );
		$percentage = 100;
		if ( $total > 0 ) {
			$percentage = ( ( 30 * $this->step ) / $total ) * 100;
		}
		if ( $percentage > 100 ) {
			$percentage = 100;
		}

		return $percentage;
	}

	/**
	 * Print the CSV rows for the current step.
	 *
	 * @access public
	 *
	 * @since  2.1
	 *
	 * @return string|false
	 */
	public function print_csv_rows() {

		$row_data = '';
		$data     = $this->get_data();
		$cols     = $this->get_csv_cols();

		if ( $data ) {

			// Output each row
			foreach ( $data as $row ) {
				$i = 1;
				foreach ( $row as $col_id => $column ) {
					// Make sure the column is valid
					if ( array_key_exists( $col_id, $cols ) ) {
						$row_data .= '"' . preg_replace( '/"/', "'", $column ) . '"';
						$row_data .= $i == count( $cols ) ? '' : ',';
						$i ++;
					}
				}
				$row_data .= "\r\n";
			}

			$this->stash_step_data( $row_data );

			return $row_data;
		}

		return false;
	}
}
