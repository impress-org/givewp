<?php
/**
 * Donations Import Class
 *
 * This class handles donations import.
 *
 * @package     Give
 * @subpackage  Classes/Give_Import_Donations
 * @copyright   Copyright (c) 2017, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.8.14
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'Give_Import_Donations' ) ) {

	/**
	 * Give_Import_Donations.
	 *
	 * @since 1.8.14
	 */
	final class Give_Import_Donations {

		/**
		 * Importer type
		 *
		 * @since 1.8.13
		 * @var string
		 */
		private $importer_type = 'import_donations';

		/**
		 * Instance.
		 *
		 * @since
		 * @access private
		 * @var
		 */
		private static $instance;

		/**
		 * Importing donation per page.
		 *
		 * @since 1.8.14
		 *
		 * @var   int
		 */
		public static $per_page = 25;

		/**
		 * Importing donation per page.
		 *
		 * @since 2.1
		 *
		 * @var   int
		 */
		public $is_csv_valid = false;

		/**
		 * Singleton pattern.
		 *
		 * @since
		 * @access private
		 */
		private function __construct() {
			self::$per_page = ! empty( $_GET['per_page'] ) ? absint( $_GET['per_page'] ) : self::$per_page;
		}

		/**
		 * Get instance.
		 *
		 * @since
		 * @access public
		 *
		 * @return static
		 */
		public static function get_instance() {
			if ( null === static::$instance ) {
				self::$instance = new static();
			}

			return self::$instance;
		}

		/**
		 * Setup
		 *
		 * @since 1.8.14
		 *
		 * @return void
		 */
		public function setup() {
			$this->setup_hooks();
		}


		/**
		 * Setup Hooks.
		 *
		 * @since 1.8.14
		 *
		 * @return void
		 */
		private function setup_hooks() {
			if ( ! $this->is_donations_import_page() ) {
				return;
			}

			// Do not render main import tools page.
			remove_action( 'give_admin_field_tools_import', array( 'Give_Settings_Import', 'render_import_field' ) );

			// Render donation import page
			add_action( 'give_admin_field_tools_import', array( $this, 'render_page' ) );

			// Print the HTML.
			add_action( 'give_tools_import_donations_form_start', array( $this, 'html' ), 10 );

			// Run when form submit.
			add_action( 'give-tools_save_import', array( $this, 'save' ) );

			add_action( 'give-tools_update_notices', array( $this, 'update_notices' ), 11, 1 );

			// Used to add submit button.
			add_action( 'give_tools_import_donations_form_end', array( $this, 'submit' ), 10 );
		}

		/**
		 * Update notice
		 *
		 * @since 1.8.14
		 *
		 * @param $messages
		 *
		 * @return mixed
		 */
		public function update_notices( $messages ) {
			if ( ! empty( $_GET['tab'] ) && 'import' === give_clean( $_GET['tab'] ) ) {
				unset( $messages['give-setting-updated'] );
			}

			return $messages;
		}

		/**
		 * Print submit and nonce button.
		 *
		 * @since 1.8.14
		 */
		public function submit() {
			wp_nonce_field( 'give-save-settings', '_give-save-settings' );
			?>
			<input type="hidden" class="import-step" id="import-step" name="step"
				   value="<?php echo esc_attr($this->get_step()); ?>"/>
			<input type="hidden" class="importer-type" value="<?php echo esc_attr($this->importer_type); ?>"/>
			<?php
		}

		/**
		 * Print the HTML for importer.
		 *
		 * @since 1.8.14
		 */
		public function html() {
			$step = $this->get_step();

			// Show progress.
			$this->render_progress();
			?>
			<section>
				<table
						class="widefat export-options-table give-table <?php echo esc_attr("step-{$step}"); ?> <?php echo esc_attr(( 1 === $step && ! empty( $this->is_csv_valid ) ? 'give-hidden' : '' )); ?>  "
						id="<?php echo esc_attr("step-{$step}"); ?>">
					<tbody>
					<?php
					switch ( $step ) {
						case 1:
							$this->render_media_csv();
							break;

						case 2:
							$this->render_dropdown();
							break;

						case 3:
							$this->start_import();
							break;

						case 4:
							$this->import_success();
					}
					if ( false === $this->check_for_dropdown_or_import() ) {
						?>
						<tr valign="top">
							<th>
								<input type="submit"
									   class="button button-primary button-large button-secondary <?php echo esc_attr("step-{$step}"); ?>"
									   id="recount-stats-submit"
									   value="
										   <?php
											/**
											 * Filter to modify donation importer submit button text.
											 *
											 * @since 2.1
											 */
											echo esc_attr(apply_filters( 'give_import_donation_submit_button_text', __( 'Submit', 'give' ) ));
											?>
											"/>
							</th>
							<th>
								<?php
								/**
								 * Action to add submit button description.
								 *
								 * @since 2.1
								 */
								do_action( 'give_import_donation_submit_button' );
								?>
							</th>
						</tr>
						<?php
					}
					?>
					</tbody>
				</table>
			</section>
			<?php
		}

		/**
		 * Show success notice
		 *
         * @since 2.25.3 Add nonce check.
		 * @since 1.8.14
		 */
		public function import_success() {
            check_admin_referer('give_donation_import_success');

			$delete_csv = ( ! empty( $_GET['delete_csv'] ) ? absint( $_GET['delete_csv'] ) : false );
			$csv        = ( ! empty( $_GET['csv'] ) ? absint( $_GET['csv'] ) : false );
			if ( ! empty( $delete_csv ) && ! empty( $csv ) ) {
				wp_delete_attachment( $csv, true );
			}

			$report = give_import_donation_report();

			$report_html = array(
				'duplicate_donor'    => array(
					__( '%1$s duplicate %2$s detected', 'give' ),
					__( '%1$s duplicate %2$s detected', 'give' ),
					__( 'donor', 'give' ),
					__( 'donors', 'give' ),
				),
				'create_donor'       => array(
					__( '%1$s %2$s created', 'give' ),
					__( '%1$s %2$s will be created', 'give' ),
					__( 'donor', 'give' ),
					__( 'donors', 'give' ),
				),
				'create_form'        => array(
					__( '%1$s donation %2$s created', 'give' ),
					__( '%1$s donation %2$s will be created', 'give' ),
					__( 'form', 'give' ),
					__( 'forms', 'give' ),
				),
				'duplicate_donation' => array(
					__( '%1$s duplicate %2$s detected', 'give' ),
					__( '%1$s duplicate %2$s detected', 'give' ),
					__( 'donation', 'give' ),
					__( 'donations', 'give' ),
				),
				'create_donation'    => array(
					__( '%1$s %2$s imported', 'give' ),
					__( '%1$s %2$s will be imported', 'give' ),
					__( 'donation', 'give' ),
					__( 'donations', 'give' ),
				),
			);
			$total       = (int) $_GET['total'];
			-- $total;
			$success = (bool) $_GET['success'];
			$dry_run = empty( $_GET['dry_run'] ) ? 0 : absint( $_GET['dry_run'] );
			?>
			<tr valign="top" class="give-import-dropdown">
				<th colspan="2">
					<h2>
						<?php
						if ( $success ) {
							if ( $dry_run ) {
								printf(
									_n( 'Dry run import complete! %s donation processed', 'Dry run import complete! %s donations processed', $total, 'give' ),
									"<strong>{$total}</strong>"
								);
							} else {
								printf(
									_n( 'Import complete! %s donation processed', 'Import complete! %s donations processed', $total, 'give' ),
									"<strong>{$total}</strong>"
								);
							}
						} else {
							printf(
								_n( 'Failed to import %s donation', 'Failed to import %s donations', $total, 'give' ),
								"<strong>{$total}</strong>"
							);
						}
						?>
					</h2>

					<?php
					$text      = __( 'Import Donation', 'give' );
					$query_arg = array(
						'post_type' => 'give_forms',
						'page'      => 'give-tools',
						'tab'       => 'import',
					);
					if ( $success ) {

						if ( $dry_run ) {
							$query_arg = array(
								'post_type'     => 'give_forms',
								'page'          => 'give-tools',
								'tab'           => 'import',
								'importer-type' => 'import_donations',
							);

							$text = __( 'Start Import', 'give' );
						} else {
							$query_arg = array(
								'post_type' => 'give_forms',
								'page'      => 'give-payment-history',
							);
							$text      = __( 'View Donations', 'give' );
						}
					}

					foreach ( $report as $key => $value ) {
						if ( array_key_exists( $key, $report_html ) && ! empty( $value ) ) {
							$key_name = $report_html[ $key ][2];
							if ( $value > 1 ) {
								$key_name = $report_html[ $key ][3];
							}
							?>
							<p>
								<?php printf( $report_html[ $key ][ $dry_run ], $value, $key_name ); ?>
							</p>
							<?php
						}
					}
					?>

					<p>
						<a class="button button-large button-secondary"
						   href="<?php echo esc_url( add_query_arg( $query_arg, admin_url( 'edit.php' ) ) ); ?>"><?php echo $text; ?></a>
					</p>
				</th>
			</tr>
			<?php
		}

		/**
		 * Will start Import
		 *
		 * @since 1.8.14
		 */
		public function start_import() {
			// Reset the donation form report.
			give_import_donation_report_reset();

			$csv         = absint( $_REQUEST['csv'] );
			$delimiter   = ( ! empty( $_REQUEST['delimiter'] ) ? give_clean( $_REQUEST['delimiter'] ) : 'csv' );
			$index_start = 1;
			$next        = true;
			$total       = self::get_csv_total( $csv );
			if ( self::$per_page < $total ) {
				$total_ajax = ceil( $total / self::$per_page );
				$index_end  = self::$per_page;
			} else {
				$total_ajax = 1;
				$index_end  = $total;
				$next       = false;
			}
			$current_percentage = 100 / ( $total_ajax + 1 );

			?>
			<tr valign="top" class="give-import-dropdown">
				<th colspan="2">
					<h2 id="give-import-title"><?php _e( 'Importing', 'give' ); ?></h2>
					<p class="give-field-description"><?php _e( 'Your donations are now being imported...', 'give' ); ?></p>
				</th>
			</tr>

			<tr valign="top" class="give-import-dropdown">
				<th colspan="2">
					<span class="spinner is-active"></span>
					<div class="give-progress"
						 data-current="1"
						 data-total_ajax="<?php echo esc_attr(absint( $total_ajax )); ?>"
						 data-start="<?php echo esc_attr(absint( $index_start )); ?>"
						 data-end="<?php echo esc_attr(absint( $index_end )); ?>"
						 data-next="<?php echo esc_attr(absint( $next )); ?>"
						 data-total="<?php echo esc_attr(absint( $total )); ?>"
						 data-per_page="<?php echo esc_attr(absint( self::$per_page )); ?>">

						<div style="width: <?php echo esc_attr((float) $current_percentage); ?>%"></div>
					</div>
					<input type="hidden" value="3" name="step">
					<input type="hidden" value='<?php echo esc_attr( maybe_serialize( $_REQUEST['mapto'] ) ); ?>' name="mapto" class="mapto">
					<input type="hidden" value="<?php echo esc_attr($csv); ?>" name="csv" class="csv">
					<input type="hidden" value="<?php echo esc_attr( $_REQUEST['mode'] ); ?>" name="mode" class="mode">
					<input type="hidden" value="<?php echo esc_attr( $_REQUEST['create_user'] ); ?>" name="create_user" class="create_user">
					<input type="hidden" value="<?php echo esc_attr( $_REQUEST['delete_csv'] ); ?>" name="delete_csv" class="delete_csv">
					<input type="hidden" value="<?php echo esc_attr( $delimiter ); ?>" name="delimiter">
					<input type="hidden" value="<?php echo esc_attr(absint( $_REQUEST['dry_run']) ); ?>" name="dry_run">
					<input type="hidden" value='<?php echo esc_attr( maybe_serialize( self::get_importer( $csv, 0, $delimiter ) ) ); ?>' name="main_key" class="main_key">
				</th>
			</tr>
			<?php
		}

		/**
		 * Will return true if importing can be started or not else false.
		 *
		 * @since 1.8.14
		 */
		public function check_for_dropdown_or_import() {
			$return = true;
			if ( isset( $_REQUEST['mapto'] ) ) {
				$mapto = (array) $_REQUEST['mapto'];
				if ( false === in_array( 'form_title', $mapto ) && false === in_array( 'form_id', $mapto ) ) {
					Give_Admin_Settings::add_error( 'give-import-csv-form', __( 'In order to import donations, a column must be mapped to either the "Donation Form Title" or "Donation Form ID" field. Please map a column to one of those fields.', 'give' ) );
					$return = false;
				}

				if ( false === in_array( 'amount', $mapto ) ) {
					Give_Admin_Settings::add_error( 'give-import-csv-amount', __( 'In order to import donations, a column must be mapped to the "Amount" field. Please map a column to that field.', 'give' ) );
					$return = false;
				}

				if ( false === in_array( 'email', $mapto ) && false === in_array( 'donor_id', $mapto ) ) {
					Give_Admin_Settings::add_error( 'give-import-csv-donor', __( 'In order to import donations, a column must be mapped to either the "Donor Email" or "Donor ID" field. Please map a column to that field.', 'give' ) );
					$return = false;
				}
			} else {
				$return = false;
			}

			return $return;
		}

		/**
		 * Print the Dropdown option for CSV.
		 *
		 * @since 1.8.14
		 */
		public function render_dropdown() {
            if (!$this->is_nonce_valid()) {
                Give_Admin_Settings::add_error( 'give-import-csv', __( 'Something went wrong.', 'give' ) );
                ?>
				<input type="hidden" name="csv_not_valid" class="csv_not_valid" value="<?php echo esc_attr(give_import_page_url()); ?>"/>
				<?php
                wp_die();
            }

			$csv       = (int) $_GET['csv'];
			$delimiter = ( ! empty( $_GET['delimiter'] ) ? give_clean( $_GET['delimiter'] ) : 'csv' );

			// TO check if the CSV files that is being add is valid or not if not then redirect to first step again
			if ( ! $this->is_valid_csv( $csv ) ) {
				$url = give_import_page_url();
				?>
				<input type="hidden" name="csv_not_valid" class="csv_not_valid" value="<?php echo esc_attr($url); ?>"/>
				<?php
			} else {
				?>
				<tr valign="top" class="give-import-dropdown">
					<th colspan="2">
						<h2 id="give-import-title"><?php _e( 'Map CSV fields to donations', 'give' ); ?></h2>

						<p class="give-import-donation-required-fields-title"><?php _e( 'Required Fields', 'give' ); ?></p>

						<p class="give-field-description"><?php _e( 'These fields are required for the import to submitted', 'give' ); ?></p>

						<ul class="give-import-donation-required-fields">
							<li class="give-import-donation-required-email"
								title="Please configure all required fields to start the import process.">
								<span class="give-import-donation-required-symbol dashicons dashicons-no-alt"></span>
								<span class="give-import-donation-required-text">
									<?php
									_e( 'Email Address', 'give' );
									?>
								</span>
							</li>

							<li class="give-import-donation-required-first"
								title="Please configure all required fields to start the import process.">
								<span class="give-import-donation-required-symbol dashicons dashicons-no-alt"></span>
								<span class="give-import-donation-required-text">
									<?php
									_e( 'First Name', 'give' );
									?>
								</span>
							</li>

							<li class="give-import-donation-required-amount"
								title="Please configure all required fields to start the import process.">
								<span class="give-import-donation-required-symbol dashicons dashicons-no-alt"></span>
								<span class="give-import-donation-required-text">
									<?php
									_e( 'Donation Amount', 'give' );
									?>
								</span>
							</li>

							<li class="give-import-donation-required-form"
								title="Please configure all required fields to start the import process.">
								<span class="give-import-donation-required-symbol dashicons dashicons-no-alt"></span>
								<span class="give-import-donation-required-text">
									<?php
									_e( 'Form Title or ID', 'give' );
									?>
								</span>
							</li>
						</ul>

						<p class="give-field-description"><?php _e( 'Select fields from your CSV file to map against donations fields or to ignore during import.', 'give' ); ?></p>
					</th>
				</tr>

				<tr valign="top" class="give-import-dropdown">
					<th><b><?php _e( 'Column name', 'give' ); ?></b></th>
					<th><b><?php _e( 'Map to field', 'give' ); ?></b></th>
				</tr>

				<?php
				$raw_key = $this->get_importer( $csv, 0, $delimiter );
				$mapto   = (array) ( isset( $_REQUEST['mapto'] ) ? $_REQUEST['mapto'] : array() );

				foreach ( $raw_key as $index => $value ) {
					?>
					<tr valign="middle" class="give-import-option">
						<th><?php echo esc_html($value); ?></th>
						<th>
							<?php
							$this->get_columns( $index, $value, $mapto );
							?>
						</th>
					</tr>
					<?php
				}
			}
		}

		/**
		 * @param $option_value
		 * @param $value
		 *
		 * @return string
		 */
		public function selected( $option_value, $value ) {
			$option_value = strtolower( $option_value );
			$value        = strtolower( $value );

			$selected = '';
			if ( stristr( $value, $option_value ) ) {
				$selected = 'selected';
			} elseif ( strrpos( $value, 'give_' ) && stristr( $option_value, __( 'Import as Meta', 'give' ) ) ) {
				$selected = 'selected';
			}

			return $selected;
		}

		/**
		 * Print the columns from the CSV.
		 *
		 * @since  1.8.14
		 * @access private
		 *
		 * @param string $index
		 * @param bool   $value
		 * @param array  $mapto
		 *
		 * @return void
		 */
		private function get_columns( $index, $value = false, $mapto = array() ) {
			$default       = give_import_default_options();
			$current_mapto = (string) ( ! empty( $mapto[ $index ] ) ? $mapto[ $index ] : '' );
			?>
			<select name="mapto[<?php echo esc_attr($index); ?>]">
				<?php $this->get_dropdown_option_html( $default, $current_mapto, $value ); ?>

				<optgroup label="<?php _e( 'Donations', 'give' ); ?>">
					<?php
					$this->get_dropdown_option_html( give_import_donations_options(), $current_mapto, $value );
					?>
				</optgroup>

				<optgroup label="<?php _e( 'Donors', 'give' ); ?>">
					<?php
					$this->get_dropdown_option_html( give_import_donor_options(), $current_mapto, $value );
					?>
				</optgroup>

				<optgroup label="<?php _e( 'Forms', 'give' ); ?>">
					<?php
					$this->get_dropdown_option_html( give_import_donation_form_options(), $current_mapto, $value );
					?>
				</optgroup>

				<?php
				/**
				 * Fire the action
				 * You can use this filter to add new options.
				 *
				 * @since 1.8.15
				 */
				do_action( 'give_import_dropdown_option', $index, $value, $mapto, $current_mapto );
				?>
			</select>
			<?php
		}

		/**
		 * Print the option html for select in importer
		 *
		 * @since  1.8.15
		 * @access public
		 *
		 * @param  array  $options
		 * @param  string $current_mapto
		 * @param bool   $value
		 *
		 * @return void
		 */
		public function get_dropdown_option_html( $options, $current_mapto, $value = false ) {

			foreach ( $options as $option => $option_value ) {
				$ignore = array();
				if ( isset( $option_value['ignore'] ) && is_array( $option_value['ignore'] ) ) {
					$ignore = $option_value['ignore'];
					unset( $option_value['ignore'] );
				}

				$option_value_texts = (array) $option_value;
				$option_text        = $option_value_texts[0];

				$checked = ( ( $current_mapto === $option ) ? 'selected' : false );
				if ( empty( $checked ) && ! in_array( $value, $ignore ) ) {
					foreach ( $option_value_texts as $option_value_text ) {
						$checked = $this->selected( $option_value_text, $value );
						if ( $checked ) {
							break;
						}
					}
				}
                ?>
                    <option value="<?php echo esc_attr($option); ?>" <?php echo esc_attr($checked); ?> ><?php echo esc_html($option_text); ?></option>
                <?php
			}
		}

		/**
		 * Get column count of csv file.
		 *
		 * @since 1.8.14
		 *
		 * @param $file_id
		 *
		 * @return bool|int
		 */
		public function get_csv_total( $file_id ) {
			$total = false;
			if ( $file_id ) {
				$file_dir = get_attached_file( $file_id );
				if ( $file_dir ) {
					$total = $this->get_csv_data_from_file_dir( $file_dir );
				}
			}

			return $total;
		}

		/**
		 * Get data from File
		 *
		 * @since 2.1
		 *
		 * @param $file_dir
		 *
		 * @return bool|int
		 */
		public function get_csv_data_from_file_dir( $file_dir ) {
			$total = false;
			if ( $file_dir ) {
				$file = new SplFileObject( $file_dir, 'r' );
				$file->seek( PHP_INT_MAX );
				$total = $file->key() + 1;
			}

			return $total;
		}

		/**
		 * Get the CSV fields title from the CSV.
		 *
		 * @since 1.8.14
		 *
		 * @param (int)  $file_id
		 * @param int    $index
		 * @param string $delimiter
		 *
		 * @return array|bool $raw_data title of the CSV file fields
		 */
		public function get_importer( $file_id, $index = 0, $delimiter = 'csv' ) {
			/**
			 * Filter to modify delimiter of Import.
			 *
			 * @since 1.8.14
			 *
			 * Return string $delimiter.
			 */
			$delimiter = (string) apply_filters( 'give_import_delimiter_set', $delimiter );

			$raw_data = false;
			$file_dir = get_attached_file( $file_id );
			if ( $file_dir ) {
				if ( false !== ( $handle = fopen( $file_dir, 'r' ) ) ) {
					$raw_data = fgetcsv( $handle, $index, $delimiter );
					// Remove BOM signature from the first item.
					if ( isset( $raw_data[0] ) ) {
						$raw_data[0] = $this->remove_utf8_bom( $raw_data[0] );
					}
				}
			}

			return $raw_data;
		}

		/**
		 * Remove UTF-8 BOM signature.
		 *
		 * @since 1.8.14
		 *
		 * @param  string $string String to handle.
		 *
		 * @return string
		 */
		public function remove_utf8_bom( $string ) {
			if ( 'efbbbf' === substr( bin2hex( $string ), 0, 6 ) ) {
				$string = substr( $string, 3 );
			}

			return $string;
		}

		/**
		 * Is used to show the process when user upload the donor form.
		 *
		 * @since 1.8.14
		 */
		public function render_progress() {
			$step = $this->get_step();
			?>
			<ol class="give-progress-steps">
				<li class="<?php echo esc_attr( 1 === $step ? 'active' : '' ); ?>">
					<?php _e( 'Upload CSV file', 'give' ); ?>
				</li>
				<li class="<?php echo esc_attr( 2 === $step ? 'active' : '' ); ?>">
					<?php _e( 'Column mapping', 'give' ); ?>
				</li>
				<li class="<?php echo esc_attr( 3 === $step ? 'active' : '' ); ?>">
					<?php _e( 'Import', 'give' ); ?>
				</li>
				<li class="<?php echo esc_attr( 4 === $step ? 'active' : '' ); ?>">
					<?php _e( 'Done!', 'give' ); ?>
				</li>
			</ol>
			<?php
		}

		/**
		 * Will return the import step.
		 *
		 * @since 1.8.14
		 *
		 * @return int $step on which step doest the import is on.
		 */
		public function get_step() {
			$step    = (int) ( isset( $_REQUEST['step'] ) ? give_clean( $_REQUEST['step'] ) : 0 );
			$on_step = 1;

			if ( empty( $step ) || 1 === $step ) {
				$on_step = 1;
			} elseif ( $this->check_for_dropdown_or_import() ) {
				$on_step = 3;
			} elseif ( 2 === $step ) {
				$on_step = 2;
			} elseif ( 4 === $step ) {
				$on_step = 4;
			}

			return $on_step;
		}

		/**
		 * Render donations import page
		 *
		 * @since 1.8.14
		 */
		public function render_page() {
			include_once GIVE_PLUGIN_DIR . 'includes/admin/tools/views/html-admin-page-import-donations.php';
		}

		/**
		 * Print Dry Run HTML on donation import page
		 *
		 * @since 2.1
		 */
		public function give_import_donation_submit_button_render_media_csv() {
			$dry_run = isset( $_POST['dry_run'] ) ? absint( $_POST['dry_run'] ) : 1;
			?>
			<div>
				<label for="dry_run">
					<input type="hidden" name="dry_run" value="0"/>
					<input type="checkbox" name="dry_run" id="dry_run" class="dry_run"
						   value="1" <?php checked( 1, $dry_run ); ?> >
					<strong><?php _e( 'Dry Run', 'give' ); ?></strong>
				</label>
				<p class="give-field-description">
					<?php
					_e( 'Preview what the import would look like without making any default changes to your site or your database.', 'give' );
					?>
				</p>
			</div>
			<?php
		}

		/**
		 * Change submit button text on first step of importing donation.
		 *
		 * @since 2.1
		 *
		 * @param $text
		 *
		 * @return string
		 */
		function give_import_donation_submit_text_render_media_csv( $text ) {
			return __( 'Begin Import', 'give' );
		}

		/**
		 * Add CSV upload HTMl
		 *
		 * Print the html of the file upload from which CSV will be uploaded.
		 *
		 * @since 1.8.14
		 * @return void
		 */
		public function render_media_csv() {
			add_filter(
				'give_import_donation_submit_button_text',
				array(
					$this,
					'give_import_donation_submit_text_render_media_csv',
				)
			);
			add_action(
				'give_import_donation_submit_button',
				array(
					$this,
					'give_import_donation_submit_button_render_media_csv',
				)
			);
			?>
			<tr valign="top">
				<th colspan="2">
					<h2 id="give-import-title"><?php _e( 'Import donations from a CSV file', 'give' ); ?></h2>
					<p class="give-field-description"><?php _e( 'This tool allows you to import or add donation data to your give form(s) via a CSV file.', 'give' ); ?></p>
				</th>
			</tr>
			<?php
			$csv         = ( isset( $_POST['csv'] ) ? give_clean( $_POST['csv'] ) : '' );
			$csv_id      = ( isset( $_POST['csv_id'] ) ? give_clean( $_POST['csv_id'] ) : '' );
			$delimiter   = ( isset( $_POST['delimiter'] ) ? give_clean( $_POST['delimiter'] ) : 'csv' );
			$mode        = empty( $_POST['mode'] ) ?
				'disabled' :
				( give_is_setting_enabled( give_clean( $_POST['mode'] ) ) ? 'enabled' : 'disabled' );
			$create_user = empty( $_POST['create_user'] ) ?
				'disabled' :
				( give_is_setting_enabled( give_clean( $_POST['create_user'] ) ) ? 'enabled' : 'disabled' );
			$delete_csv  = empty( $_POST['delete_csv'] ) ?
				'enabled' :
				( give_is_setting_enabled( give_clean( $_POST['delete_csv'] ) ) ? 'enabled' : 'disabled' );

			// Reset csv and csv_id if csv
			if ( empty( $csv_id ) || ! $this->is_valid_csv( $csv_id, $csv ) ) {
				$csv_id = $csv = '';
			}
			$per_page = isset( $_POST['per_page'] ) ? absint( $_POST['per_page'] ) : self::$per_page;

			$sample_file_text = sprintf(
				'%s <a href="%s">%s</a>.',
				__( 'Download the sample file', 'give' ),
				esc_url( GIVE_PLUGIN_URL . 'sample-data/sample-data.csv' ),
				__( 'here', 'give' )
			);

			$csv_description = sprintf(
				'%1$s %2$s',
				__( 'The file must be a Comma Seperated Version (CSV) file type only.', 'give' ),
				$sample_file_text
			);

			$settings = array(
				array(
					'id'          => 'csv',
					'name'        => __( 'Choose a CSV file:', 'give' ),
					'type'        => 'file',
					'attributes'  => array(
						'editing' => 'false',
						'library' => 'text',
					),
					'description' => $csv_description,
					'fvalue'      => 'url',
					'default'     => $csv,
				),
				array(
					'id'    => 'csv_id',
					'type'  => 'hidden',
					'value' => $csv_id,
				),
				array(
					'id'          => 'delimiter',
					'name'        => __( 'CSV Delimiter:', 'give' ),
					'description' => __( 'In case your CSV file supports a different type of separator (or delimiter) -- like a tab or space -- you can set that here.', 'give' ),
					'default'     => $delimiter,
					'type'        => 'select',
					'options'     => array(
						'csv'                  => __( 'Comma', 'give' ),
						'tab-separated-values' => __( 'Tab', 'give' ),
					),
				),
				array(
					'id'          => 'mode',
					'name'        => __( 'Test Mode:', 'give' ),
					'description' => __( 'Select whether you would like these donations to be marked as "test" donations within the database. By default, they will be marked as live donations.', 'give' ),
					'default'     => $mode,
					'type'        => 'radio_inline',
					'options'     => array(
						'enabled'  => __( 'Enabled', 'give' ),
						'disabled' => __( 'Disabled', 'give' ),
					),
				),
				array(
					'id'          => 'create_user',
					'name'        => __( 'Create WP users for new donors:', 'give' ),
					'description' => __( 'The importer can create WordPress user accounts based on the names and email addresses of the donations in your CSV file. Enable this option if you\'d like the importer to do that.', 'give' ),
					'default'     => $create_user,
					'type'        => 'radio_inline',
					'options'     => array(
						'enabled'  => __( 'Enabled', 'give' ),
						'disabled' => __( 'Disabled', 'give' ),
					),
				),
				array(
					'id'          => 'delete_csv',
					'name'        => __( 'Delete CSV after import:', 'give' ),
					'description' => __( 'Your CSV file will be uploaded via the WordPress Media Library. It\'s a good idea to delete it after the import is finished so that your sensitive data is not accessible on the web. Disable this only if you plan to delete the file manually later.', 'give' ),
					'default'     => $delete_csv,
					'type'        => 'radio_inline',
					'options'     => array(
						'enabled'  => __( 'Enabled', 'give' ),
						'disabled' => __( 'Disabled', 'give' ),
					),
				),
				array(
					'id'          => 'per_page',
					'name'        => __( 'Process Rows Per Batch:', 'give' ),
					'type'        => 'number',
					'description' => __( 'Determine how many rows you would like to import per cycle.', 'give' ),
					'default'     => $per_page,
					'class'       => 'give-text-small',
				),
			);

			$settings = apply_filters( 'give_import_file_upload_html', $settings );

			if ( empty( $this->is_csv_valid ) ) {
				Give_Admin_Settings::output_fields( $settings, 'give_settings' );
			} else {
				?>
				<input type="hidden" name="is_csv_valid" class="is_csv_valid"
					   value="<?php echo esc_attr($this->is_csv_valid); ?>">
				<?php
			}
		}

		/**
		 * Run when user click on the submit button.
		 *
		 * @since 1.8.14
		 */
		public function save() {
            if (!$this->is_nonce_valid()){
                wp_die();
            }
			// Get the current step.
			$step = $this->get_step();

			// Validation for first step.
			if ( 1 === $step ) {
				$csv_id = absint( $_POST['csv_id'] );

				if ( $this->is_valid_csv( $csv_id, esc_url( $_POST['csv'] ) ) ) {

					$url = give_import_page_url(
						(array) apply_filters(
							'give_import_step_two_url',
							array(
								'step'          => '2',
								'importer-type' => $this->importer_type,
								'csv'           => $csv_id,
								'delimiter'     => isset( $_REQUEST['delimiter'] ) ? give_clean( $_REQUEST['delimiter'] ) : 'csv',
								'mode'          => empty( $_POST['mode'] ) ?
									'0' :
									( give_is_setting_enabled( give_clean( $_POST['mode'] ) ) ? '1' : '0' ),
								'create_user'   => empty( $_POST['create_user'] ) ?
									'0' :
									( give_is_setting_enabled( give_clean( $_POST['create_user'] ) ) ? '1' : '0' ),
								'delete_csv'    => empty( $_POST['delete_csv'] ) ?
									'1' :
									( give_is_setting_enabled( give_clean( $_POST['delete_csv'] ) ) ? '1' : '0' ),
								'per_page'      => isset( $_POST['per_page'] ) ? absint( $_POST['per_page'] ) : self::$per_page,
								'dry_run'       => isset( $_POST['dry_run'] ) ? absint( $_POST['dry_run'] ) : 0,
							)
						)
					);

					$this->is_csv_valid = wp_nonce_url($url, 'give-save-settings', '_give-save-settings');
				}
			}
		}

		/**
		 * Check if user uploaded csv is valid or not.
		 *
		 * @since  1.8.14
		 * @access public
		 *
		 * @param mixed  $csv       ID of the CSV files.
		 * @param string $match_url ID of the CSV files.
		 *
		 * @return bool $has_error CSV is valid or not.
		 */
		private function is_valid_csv( $csv = false, $match_url = '' ) {
			$is_valid_csv = true;

			if ( $csv ) {
				$csv_url = wp_get_attachment_url( $csv );

				$delimiter = ( ! empty( $_REQUEST['delimiter'] ) ? give_clean( $_REQUEST['delimiter'] ) : 'csv' );

				if (
					! $csv_url ||
					( ! empty( $match_url ) && ( $csv_url !== $match_url ) ) ||
					( ( $mime_type = get_post_mime_type( $csv ) ) && ! strpos( $mime_type, $delimiter ) )
				) {
					$is_valid_csv = false;
					Give_Admin_Settings::add_error( 'give-import-csv', __( 'Please upload or provide a valid CSV file.', 'give' ) );
				}
			} else {
				$is_valid_csv = false;
				Give_Admin_Settings::add_error( 'give-import-csv', __( 'Please upload or provide a valid CSV file.', 'give' ) );
			}

			return $is_valid_csv;
		}


		/**
		 * Render report import field
		 *
		 * @since  1.8.14
		 * @access public
		 *
		 * @param $field
		 * @param $option_value
		 */
		public function render_import_field( $field, $option_value ) {
			include_once GIVE_PLUGIN_DIR . 'includes/admin/tools/views/html-admin-page-imports.php';
		}

		/**
		 * Get if current page import donations page or not
		 *
		 * @since 1.8.14
		 * @return bool
		 */
		private function is_donations_import_page() {
			return 'import' === give_get_current_setting_tab() &&
				   isset( $_GET['importer-type'] ) &&
				   $this->importer_type === give_clean( $_GET['importer-type'] );
		}

         /**
         * @since 2.25.2
         */
        private function is_nonce_valid()
        {
            return !empty($_REQUEST['_give-save-settings']) && wp_verify_nonce($_REQUEST['_give-save-settings'], 'give-save-settings');
        }
	}

	Give_Import_Donations::get_instance()->setup();
}
