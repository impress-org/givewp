<?php
/**
 * Give Settings Page/Tab
 *
 * @package     Give
 * @subpackage  Classes/Give_Settings_Import
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'Give_Settings_Import' ) ) {

	/**
	 * Give_Settings_Import.
	 *
	 * Add a submenu page in give tools menu called Import donations which import the donations from the CSV files.
	 *
	 * @since 1.8.13
	 */
	class Give_Settings_Import extends Give_Settings_Page {

		/**
		 * Setting page id.
		 *
		 * @since 1.8.13
		 *
		 * @var   string
		 */
		protected $id = '';

		/**
		 * Setting page label.
		 *
		 * @since 1.8.13
		 *
		 * @var   string
		 */
		protected $label = '';

		/**
		 * Importing donation per page.
		 *
		 * @since 1.8.13
		 *
		 * @var   int
		 */
		public static $per_page = 5;

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->id    = 'import';
			$this->label = __( 'Import Donations', 'give' );

			// Add Import tab in submenu.
			add_filter( 'give-tools_tabs_array', array( $this, 'add_settings_page' ), 20 );
			// Will display html of the import donation.
			add_action( 'give_admin_field_tools_import', array( $this, 'render_import_field' ), 10, 2 );
			// Will call the function that genetrated the hook called 'give_admin_field_tools_import'.
			add_action( "give-tools_settings_{$this->id}_page", array( $this, 'output' ) );
			// Do not use main form for this tab.
			if ( give_get_current_setting_tab() === $this->id ) {
				add_action( "give-tools_open_form", '__return_empty_string' );
				add_action( "give-tools_close_form", '__return_empty_string' );
			}
			// Run when form submit.
			add_action( 'give-tools_save_import', array( $this, 'save' ) );
			add_action( 'give-tools_update_notices', array( $this, 'update_notices' ), 11, 1 );


			// Will add the progress of the import
			add_action( 'give_tools_import_form_before_start', array( 'Give_Settings_Import', 'progress' ), 10 );
			// Print the HTML.
			add_action( 'give_tools_import_form_start', array( 'Give_Settings_Import', 'html' ), 10 );
			// Used to add submit button.
			add_action( 'give_tools_import_form_end', array( 'Give_Settings_Import', 'submit' ), 10 );
			// Print the html for CSV file upload.
			add_action( 'give_admin_field_media_csv', array( $this, 'render_media_csv' ), 10, 2 );
		}

		static function update_notices( $messages ) {
			if ( ! empty( $_GET['tab'] ) && 'import' === give_clean( $_GET['tab'] ) ) {
				unset( $messages['give-setting-updated'] );
			}

			return $messages;
		}

		/**
		 * Print submit and nonce button.
		 *
		 * @since 1.8.13
		 */
		static function submit() {
			wp_nonce_field( 'give-save-settings', '_give-save-settings' );
			?>
			<input type="hidden" class="import-step" id="import-step" name="step"
			       value="<?php echo Give_Settings_Import::get_step(); ?>"/>
			<?php
		}

		/**
		 * Print the HTML for importer.
		 *
		 * @since 1.8.13
		 */
		static function html() {
			$step = Give_Settings_Import::get_step();
			?>
			<section>
				<table class="widefat export-options-table give-table <?php echo 'step-' . $step; ?>"
				       id="<?php echo 'step-' . $step; ?>">
					<tbody>
					<?php
					if ( 1 === $step ) {
						// Get the html of CSV file upload.
						Give_Settings_Import::render_media_csv();
					} elseif ( 2 === $step ) {
						Give_Settings_Import::render_dropdown();
					} elseif ( 3 === $step ) {
						// Drop down for importer files.
						Give_Settings_Import::start_import();
					} elseif ( 4 === $step ) {
						// Successful or fail message.
						Give_Settings_Import::import_success();
					}

					if ( self::check_for_dropdown_or_import() == false ) {
						$step = Give_Settings_Import::get_step();
						?>
						<tr valign="top">
							<th></th>
							<th>
								<input type="submit"
								       class="button button-primary button-large button-secondary <?php echo 'step-' . $step; ?>"
								       id="recount-stats-submit"
								       value="<?php esc_attr_e( 'Submit', 'give' ); ?>"/>
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

		static function import_success() {

			$delete_csv = ( ! empty( $_GET['delete_csv'] ) ? absint( $_GET['delete_csv'] ) : false );
			$csv        = ( ! empty( $_GET['csv'] ) ? absint( $_GET['csv'] ) : false );
			if ( ! empty( $delete_csv ) && ! empty( $csv ) ) {
				wp_delete_attachment( $csv, true );
			}

			$report      = give_import_donation_report();
			$report_html = array(
				'duplicate_donor'    => array(
					__( '%s duplicate %s detected', 'give' ),
					__( 'donor', 'give' ),
					__( 'donors', 'give' ),
				),
				'create_donor'       => array(
					__( '%s %s created', 'give' ),
					__( 'donor', 'give' ),
					__( 'donors', 'give' ),
				),
				'create_form'        => array(
					__( '%s donation %s created', 'give' ),
					__( 'form', 'give' ),
					__( 'forms', 'give' ),
				),
				'duplicate_donation' => array(
					__( '%s duplicate %s detected', 'give' ),
					__( 'donation', 'give' ),
					__( 'donations', 'give' ),
				),
				'create_donation'    => array(
					__( '%s %s imported', 'give' ),
					__( 'donation', 'give' ),
					__( 'donations', 'give' ),
				),
			);
			$total       = (int) $_GET['total'];
			$total       = $total - 1;
			$success     = (bool) $_GET['success'];
			?>
			<tr valign="top" class="give-import-dropdown">
				<th colspan="2">
					<h2>
						<?php
						if ( $success ) {
							echo sprintf( __( 'Import complete! %s donations processed', 'give' ), "<strong>{$total}</strong>" );
						} else {
							echo sprintf( __( 'Failed to import %s donations', 'give' ), "<strong>{$total}</strong>" );
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
						$query_arg = array(
							'post_type' => 'give_forms',
							'page'      => 'give-payment-history',
						);
						$text      = __( 'View Donations', 'give' );
					}

					foreach ( $report as $key => $value ) {
						if ( array_key_exists( $key, $report_html ) && ! empty( $value ) ) {
							?>
							<p>
								<?php echo esc_html( wp_sprintf( $report_html[ $key ][0], $value, _n( $report_html[ $key ][1], $report_html[ $key ][2], $value, 'give' ) ) ); ?>
							</p>
							<?php
						}
					}
					?>

					<p>
						<a class="button button-large button-secondary" href="<?php echo add_query_arg( $query_arg, admin_url( 'edit.php' ) ); ?>"><?php echo $text; ?></a>
					</p>
				</th>
			</tr>
			<?php
		}

		/**
		 * Will start Import
		 */
		static function start_import() {
			// Reset the donation form report.
			give_import_donation_report_reset();

			$csv         = (int) $_REQUEST['csv'];
			$index_start = 1;
			$index_end   = 1;
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
					<h2 id="give-import-title"><?php esc_html_e( 'Importing', 'give' ) ?></h2>
					<p>
						<?php esc_html_e( 'Your donations are now being imported...', 'give' ) ?>
					</p>
				</th>
			</tr>

			<tr valign="top" class="give-import-dropdown">
				<th colspan="2">
					<span class="spinner is-active"></span>
					<div class="give-progress"
					     data-current="1"
					     data-total_ajax="<?php echo $total_ajax; ?>"
					     data-start="<?php echo $index_start; ?>"
					     data-end="<?php echo $index_end; ?>"
					     data-next="<?php echo $next; ?>"
					     data-total="<?php echo $total; ?>"
					     data-per_page="<?php echo self::$per_page; ?>">

						<div style="width: <?php echo $current_percentage; ?>%"></div>
					</div>
					<input type="hidden" value="3" name="step">
					<input type="hidden" value='<?php echo maybe_serialize( $_REQUEST['mapto'] ); ?>' name="mapto"
					       class="mapto">
					<input type="hidden" value="<?php echo $_REQUEST['csv']; ?>" name="csv" class="csv">
					<input type="hidden" value="<?php echo $_REQUEST['mode']; ?>" name="mode" class="mode">
					<input type="hidden" value="<?php echo $_REQUEST['create_user']; ?>" name="create_user"
					       class="create_user">
					<input type="hidden" value="<?php echo $_REQUEST['delete_csv']; ?>" name="delete_csv"
					       class="delete_csv">
					<input type="hidden" value="<?php echo $_REQUEST['delimiter']; ?>" name="delimiter">
					<input type="hidden" value='<?php echo maybe_serialize( self::get_importer( $csv ) ); ?>'
					       name="main_key"
					       class="main_key">
				</th>
			</tr>

			<script type="text/javascript">
				jQuery(document).ready(function ($) {
					give_on_donation_import_start();
				});
			</script>
			<?php
		}

		/**
		 * Will return true if importing can be started or not else false.
		 *
		 * @since 1.8.13
		 */
		static function check_for_dropdown_or_import() {
			$return = true;
			if ( isset( $_REQUEST['mapto'] ) ) {
				$mapto = (array) $_REQUEST['mapto'];
				if ( false === in_array( 'form_title', $mapto ) && false === in_array( 'form_id', $mapto ) ) {
					Give_Admin_Settings::add_error( 'give-import-csv-form', __( 'Please select Form ID or Form Name options from the dropdown.', 'give' ) );
					$return = false;
				}

				if ( false === in_array( 'amount', $mapto ) ) {
					Give_Admin_Settings::add_error( 'give-import-csv-amount', __( 'Please select Amount option from the dropdown.', 'give' ) );
					$return = false;
				}

				if ( false === in_array( 'email', $mapto ) && false === in_array( 'donor_id', $mapto ) ) {
					Give_Admin_Settings::add_error( 'give-import-csv-donor', __( 'Please select Email id or Customer ID options from the dropdown.', 'give' ) );
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
		 * @since 1.8.13
		 */
		static function render_dropdown() {
			$csv = (int) $_GET['csv'];

			// TO check if the CSV files that is being add is valid or not if not then redirect to first step again
			$has_error = self::csv_check( $csv );
			if ( $has_error ) {
				$url = give_import_page_url();
				?>
				<script type="text/javascript">
					window.location = "<?php echo $url; ?>"
				</script>
				<?php
			} else {
				?>
				<tr valign="top" class="give-import-dropdown">
					<th colspan="2">
						<h2 id="give-import-title"><?php esc_html_e( 'Map CSV fields to donations', 'give' ) ?></h2>
						<p><?php esc_html_e( 'Select fields from your CSV file to map against donations fields or to ignore during import.', 'give' ) ?></p>
					</th>
				</tr>

				<tr valign="top" class="give-import-dropdown">
					<th><b><?php esc_html_e( 'Column name', 'give' ); ?></b></th>
					<th><b><?php esc_html_e( 'Map to field', 'give' ); ?></b></th>
				</tr>

				<?php
				$raw_key   = self::get_importer( $csv );
				$donations = give_import_donations_options();
				$donors    = give_import_donor_options();
				$forms     = give_import_donation_form_options();

				foreach ( $raw_key as $index => $value ) {
					?>
					<tr valign="top" class="give-import-option">
						<th><?php echo $value; ?></th>
						<th>
							<?php
							self::get_columns( $index, $donations, $donors, $forms, $value );
							?>
						</th>
					</tr>
					<?php
				}
			}
		}

		static function selected( $option_value, $value ) {
			$selected = '';
			if ( stristr( $value, $option_value ) ) {
				$selected = 'selected';
			} elseif ( strrpos( $value, '_' ) && stristr( $option_value, 'Import as Meta' ) ) {
				$selected = 'selected';
			}

			return $selected;
		}

		/**
		 * Print the colums from the CSV.
		 *
		 * @since 1.8.13
		 */
		static function get_columns( $index, $donations, $donors, $forms, $value = false ) {
			$default = give_import_default_options();
			?>
			<select name="mapto[<?php echo $index; ?>]">
				<?php
				foreach ( $default as $option => $option_value ) {
					$checked = self::selected( $option_value, $value );
					?>
					<option value="<?php echo $option; ?>" <?php echo $checked; ?> ><?php echo $option_value; ?></option>
					<?php
				}
				?>
				<optgroup label="Donations">
					<?php
					foreach ( $donations as $option => $option_value ) {
						$checked = self::selected( $option_value, $value );
						?>
						<option value="<?php echo $option; ?>" <?php echo $checked; ?> ><?php echo $option_value; ?></option>
						<?php
					}
					?>
				</optgroup>

				<optgroup label="Donors">
					<?php
					foreach ( $donors as $option => $option_value ) {
						$checked = self::selected( $option_value, $value );
						?>
						<option value="<?php echo $option; ?>" <?php echo $checked; ?> ><?php echo $option_value; ?></option>
						<?php
					}
					?>
				</optgroup>

				<optgroup label="Forms">
					<?php
					foreach ( $forms as $option => $option_value ) {
						$checked = self::selected( $option_value, $value );
						?>
						<option value="<?php echo $option; ?>" <?php echo $checked; ?> ><?php echo $option_value; ?></option>
						<?php
					}
					?>
				</optgroup>

				<?php
				do_action( 'give_import_dropdown_option', $index, $donations, $donors, $forms, $value );
				?>
			</select>
			<?php
		}

		static function get_csv_total( $file_id ) {
			$total = false;
			if ( $file_id ) {
				$file_dir = get_attached_file( $file_id );
				if ( $file_dir ) {
					$file = new SplFileObject( $file_dir, 'r' );
					$file->seek( PHP_INT_MAX );
					$total = $file->key() + 1;
				}
			}

			return $total;
		}

		/**
		 * Get the CSV fields title from the CSV.
		 *
		 * @since 1.8.13
		 *
		 * @param (int) $file_id
		 * @param int $index
		 * @param string $delimiter
		 *
		 * @return array|bool $raw_data title of the CSV file fields
		 */
		static function get_importer( $file_id, $index = 0, $delimiter = ',' ) {
			$raw_data = false;
			$file_dir = get_attached_file( $file_id );
			if ( $file_dir ) {
				if ( false !== ( $handle = fopen( $file_dir, 'r' ) ) ) {
					$raw_data = fgetcsv( $handle, $index, $delimiter );
					// Remove BOM signature from the first item.
					if ( isset( $raw_data[0] ) ) {
						$raw_data[0] = self::remove_utf8_bom( $raw_data[0] );
					}
				}
			}

			return $raw_data;
		}

		/**
		 * Remove UTF-8 BOM signature.
		 *
		 * @since 1.8.13
		 *
		 * @param  string $string String to handle.
		 *
		 * @return string
		 */
		static function remove_utf8_bom( $string ) {
			if ( 'efbbbf' === substr( bin2hex( $string ), 0, 6 ) ) {
				$string = substr( $string, 3 );
			}

			return $string;
		}


		/**
		 * Is used to show the process when user upload the donor form.
		 *
		 * @since 1.8.13
		 */
		static function progress() {
			$step = Give_Settings_Import::get_step();
			?>
			<ol class="give-progress-steps">
				<li class="<?php echo( 1 === $step ? 'active' : '' ); ?>"><?php esc_html_e( 'Upload CSV file', 'give' ); ?></li>
				<li class="<?php echo( 2 === $step ? 'active' : '' ); ?>"><?php esc_html_e( 'Column mapping', 'give' ); ?></li>
				<li class="<?php echo( 3 === $step ? 'active' : '' ); ?>"><?php esc_html_e( 'Import', 'give' ); ?></li>
				<li class="<?php echo( 4 === $step ? 'active' : '' ); ?>"><?php esc_html_e( 'Done!', 'give' ); ?></li>
			</ol>
			<?php
		}

		/**
		 * Will return the import step.
		 *
		 * @since 1.8.13
		 *
		 * @return int $step on which step doest the import is on.
		 */
		static function get_step() {
			$step    = (int) ( isset( $_REQUEST['step'] ) ? give_clean( $_REQUEST['step'] ) : 0 );
			$on_step = 1;
			if ( empty( $step ) || 1 === $step ) {
				$on_step = 1;
			} elseif ( self::check_for_dropdown_or_import() ) {
				$on_step = 3;
			} elseif ( 2 === $step ) {
				$on_step = 2;
			} elseif ( 4 === $step ) {
				$on_step = 4;
			}

			return (int) $on_step;
		}

		/**
		 * Add CSV upload HTMl
		 *
		 * Print the html of the file upload from which CSV will be uploaded.
		 *
		 * @since 1.8.13
		 */
		static public function render_media_csv() {
			?>
			<tr valign="top">
				<th colspan="2">
					<h2 id="give-import-title"><?php esc_html_e( 'Import donations from a CSV file', 'give' ) ?></h2>
					<p><?php esc_html_e( 'This tool allows you to import (or merge) donation data to give from a CSV file.', 'give' ) ?></p>
				</th>
			</tr>
			<?php
			$csv         = ( isset( $_REQUEST['csv'] ) ? give_clean( $_POST['csv'] ) : '' );
			$delimiter   = ( isset( $_REQUEST['delimiter'] ) ? give_clean( $_POST['delimiter'] ) : ',' );
			$mode        = ( ! empty( $_REQUEST['mode'] ) ? 'on' : '' );
			$create_user = ( isset( $_REQUEST['create_user'] ) && isset( $_REQUEST['csv'] ) && 1 == absint( $_REQUEST['create_user'] ) ? 'on' : ( isset( $_REQUEST['csv'] ) ? '' : 'on' ) );
			$delete_csv = ( isset( $_REQUEST['delete_csv'] ) && isset( $_REQUEST['csv'] ) && 1 == absint( $_REQUEST['delete_csv'] ) ? 'on' : ( isset( $_REQUEST['csv'] ) ? '' : 'on' ) );

			$settings = array(
				array(
					'id'         => 'csv',
					'name'       => __( 'Choose a CSV file:', 'give' ),
					'type'       => 'file',
					'attributes' => array( 'editing' => 'false', 'library' => 'text' ),
					'fvalue'     => 'id',
					'default'    => $csv,
				),
				array(
					'id'         => 'delimiter',
					'name'       => __( 'CSV Delimiter:', 'give' ),
					'type'       => 'text',
					'attributes' => array( 'placeholder' => ',', 'size' => '2' ),
					'default'    => $delimiter,
				),
				array(
					'id'      => 'mode',
					'name'    => __( 'Test Mode:', 'give' ),
					'type'    => 'checkbox',
					'default' => $mode,
				),
				array(
					'id'      => 'create_user',
					'name'    => __( 'Create WP users for new donors?:', 'give' ),
					'type'    => 'checkbox',
					'default' => $create_user,
				),
				array(
					'id'      => 'delete_csv',
					'name'    => __( 'Delete CSV after import:', 'give' ),
					'type'    => 'checkbox',
					'default' => $delete_csv,
				),
			);

			$settings = apply_filters( 'give_import_file_upload_html', $settings );

			Give_Admin_Settings::output_fields( $settings, 'give_settings' );
		}

		/**
		 * Run when user click on the submit button.
		 *
		 * @since 1.8.13
		 */
		public function save() {
			// Get the current step.
			$step = Give_Settings_Import::get_step();

			// Validation for first step.
			if ( 1 === $step ) {
				$csv = absint( $_POST['csv'] );

				$has_error = self::csv_check( $csv );

				if ( false == $has_error ) {

					$url = give_import_page_url( (array) apply_filters( 'give_import_step_two_url', array(
						'step'        => '2',
						'csv'         => $csv,
						'delimiter'   => ( isset( $_REQUEST['delimiter'] ) ) ? give_clean( $_REQUEST['delimiter'] ) : ',',
						'mode'        => ( isset( $_REQUEST['mode'] ) ) ? give_clean( $_REQUEST['mode'] ) : '0',
						'create_user' => ( isset( $_REQUEST['create_user'] ) ) ? give_clean( $_REQUEST['create_user'] ) : '0',
						'delete_csv'  => ( isset( $_REQUEST['delete_csv'] ) ) ? give_clean( $_REQUEST['delete_csv'] ) : '0',
					) ) );
					?>
					<script type="text/javascript">
						window.location = "<?php echo $url; ?>"
					</script>
					<?php
				}
			}
		}

		/**
		 * Check if user uploaded csv is valid or not.
		 *
		 * @since 1.8.13
		 *
		 * param int $csv Id of the CSV files.
		 *
		 * return bool $has_error CSV is valid or not.
		 */
		static function csv_check( $csv = false ) {
			$has_error = false;
			if ( $csv ) {
				if ( ! wp_get_attachment_url( $csv ) ) {
					$has_error = true;
					Give_Admin_Settings::add_error( 'give-import-csv', __( 'Please upload or provide the ID to a valid CSV file.', 'give' ) );
				} elseif ( ( $mime_type = get_post_mime_type( $csv ) ) && ! strpos( $mime_type, 'csv' ) ) {
					$has_error = true;
					Give_Admin_Settings::add_error( 'give-import-csv', __( 'Please upload or provide the ID to a valid CSV file.', 'give' ) );
				}
			} else {
				$has_error = true;
				Give_Admin_Settings::add_error( 'give-import-csv', __( 'Please upload or provide the ID to a valid CSV file.', 'give' ) );
			}

			return $has_error;
		}

		/**
		 * Add this page to settings.
		 *
		 * @since  1.8.13
		 *
		 * @param  array $pages Lst of pages.
		 *
		 * @return array
		 */
		public function add_settings_page( $pages ) {
			$pages[ $this->id ] = $this->label;

			return $pages;
		}

		/**
		 * Get settings array.
		 *
		 * @since  1.8.13
		 * @return array
		 */
		public function get_settings() {
			// Hide save button.
			$GLOBALS['give_hide_save_button'] = true;

			/**
			 * Filter the settings.
			 *
			 * @since  1.8.13
			 *
			 * @param  array $settings
			 */
			$settings = apply_filters(
				'give_get_settings_' . $this->id,
				array(
					array(
						'id'   => 'import',
						'name' => __( 'Import Donations', 'give' ),
						'type' => 'tools_import',
					),
				)
			);

			// Output.
			return $settings;
		}

		/**
		 * Output the settings.
		 *
		 * @since  1.8.13
		 * @return void
		 */
		public function output() {
			$settings = $this->get_settings();
			Give_Admin_Settings::output_fields( $settings, 'give_settings' );
		}

		/**
		 * Render report import field
		 *
		 * @since  1.8.13
		 * @access public
		 *
		 * @param $field
		 * @param $option_value
		 */
		public function render_import_field( $field, $option_value ) {
			include_once( 'views/html-admin-page-imports.php' );
		}
	}
}
return new Give_Settings_Import();
