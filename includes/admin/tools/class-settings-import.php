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
	 * @sine 1.8
	 */
	class Give_Settings_Import extends Give_Settings_Page {

		/**
		 * Setting page id.
		 *
		 * @since 1.8
		 * @var   string
		 */
		protected $id = '';

		/**
		 * Setting page label.
		 *
		 * @since 1.8
		 * @var   string
		 */
		protected $label = '';


		public static $per_page = 2;

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->id    = 'import';
			$this->label = __( 'Import Donations', 'give-manual-donations' );

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
			if ( ! empty( $_GET['tab'] ) && 'import' === (string) sanitize_text_field( $_GET['tab'] ) ) {
				unset( $messages['give-setting-updated'] );
			}

			return $messages;
		}

		/**
		 * Print submit and nonce button.
		 *
		 * @since 1.2.
		 */
		static function submit() {
			wp_nonce_field( 'give-save-settings', '_give-save-settings' );
			?>
            <input type="hidden" class="import-step" id="import-step" name="step"
                   value="<?php echo Give_Settings_Import::get_step(); ?>"/>

			<?php
			if ( ! self::check_for_dropdown_or_import() ) {
				?>
                <input type="submit" class="button-secondary" id="recount-stats-submit"
                       value="<?php esc_attr_e( 'Submit', 'give-manual-donations' ); ?>"/>
				<?php
			}
		}

		/**
		 * Print the HTML for importer.
		 *
		 * @since 1.2
		 */
		static function html() {
			$step = Give_Settings_Import::get_step();
			?>
            <section>
                <table class="widefat export-options-table give-table">
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
					?>
                    </tbody>
                </table>
            </section>
			<?php
		}

		static function import_success() {
			echo 'imported successfully';
		}

		/**
		 * Will start Import
		 */
		static function start_import() {
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
                    <h2><?php esc_html_e( 'Importing', 'give-manual-donations' ) ?></h2>
                    <p>
						<?php esc_html_e( 'Your donations are now being imported...', 'give-manual-donations' ) ?>
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
                    <input type="hidden" value="<?php echo $_REQUEST['existing']; ?>" name="existing" class="existing">
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
		 */
		static function check_for_dropdown_or_import() {
			if ( isset( $_REQUEST['mapto'] ) ) {
				return true;
			}

			return false;
		}

		/**
		 * Print the Dropdown option for CSV.
		 */
		static function render_dropdown() {
			$csv = (int) $_GET['csv'];
			?>
            <tr valign="top" class="give-import-dropdown">
                <th colspan="2">
                    <h2><?php esc_html_e( 'Map CSV fields to donations', 'give-manual-donations' ) ?></h2>
                    <p><?php esc_html_e( 'Select fields from your CSV file to map against donations fields, or to ignore during import.', 'give-manual-donations' ) ?></p>
                </th>
            </tr>

            <tr valign="top" class="give-import-dropdown">
                <th><b><?php esc_html_e( 'Column name', 'give-manual-donations' ); ?></b></th>
                <th><b><?php esc_html_e( 'Map to field', 'give-manual-donations' ); ?></b></th>
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
		 */
		static function get_columns( $index, $donations, $donors, $forms, $value = false ) {
			?>
            <select name="mapto[<?php echo $index; ?>]">
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
		 * @since 1.2
		 */
		static function progress() {
			$step = Give_Settings_Import::get_step();
			?>
            <ol class="give-progress-steps">
                <li class="<?php echo( 1 === $step ? 'active' : '' ); ?>">Upload CSV file</li>
                <li class="<?php echo( 2 === $step ? 'active' : '' ); ?>">Column mapping</li>
                <li class="<?php echo( 3 === $step ? 'active' : '' ); ?>">Import</li>
                <li class="<?php echo( 4 === $step ? 'active' : '' ); ?>">Done!</li>
            </ol>
			<?php
		}

		/**
		 * Will return the import step.
		 *
		 * @since 1.2
		 *
		 * @return int $step on which step doest the import is on.
		 */
		static function get_step() {
			$step    = (int) ( isset( $_REQUEST['step'] ) ? sanitize_text_field( $_REQUEST['step'] ) : false );
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
		 */
		static public function render_media_csv() {
			?>
            <tr valign="top">
                <th colspan="2">
                    <h2><?php esc_html_e( 'Import donations from a CSV file', 'give-manual-donations' ) ?></h2>
                    <p><?php esc_html_e( 'This tool allows you to import (or merge) donation data to your donor from a CSV file.', 'give-manual-donations' ) ?></p>
                </th>
            </tr>
			<?php
			$csv       = ( isset( $_REQUEST['csv'] ) ? sanitize_text_field( $_POST['csv'] ) : '' );
			$existing  = ( isset( $_REQUEST['existing'] ) ? 'on' : '' );
			$delimiter = ( isset( $_REQUEST['delimiter'] ) ? sanitize_text_field( $_POST['delimiter'] ) : '' );

			$settings = array(
				array(
					'id'      => 'csv',
					'name'    => __( 'Choose a CSV file:', 'give-manual-donations' ),
					'type'    => 'file',
					'fvalue'  => 'id',
					'default' => $csv,
				),
				array(
					'id'      => 'existing',
					'name'    => __( 'Update existing donations:', 'give-manual-donations' ),
					'type'    => 'checkbox',
					'default' => $existing,
					'decs'    => __( 'If a product being imported matches an existing donation by ID, update the existing donation rather than creating a new donation or skipping the row.', 'give-manual-donations' ),
				),
				array(
					'id'         => 'delimiter',
					'name'       => __( 'CSV Delimiter:', 'give-manual-donations' ),
					'type'       => 'text',
					'attributes' => array( 'placeholder' => ',', 'size' => '2' ),
					'default'    => $delimiter,
					'decs'       => __( 'If a product being imported matches an existing donation by ID, update the existing donation rather than creating a new donation or skipping the row.', 'give-manual-donations' ),
				),
			);

			Give_Admin_Settings::output_fields( $settings, 'give_settings' );
		}

		/**
		 * Run when user click on the submit button.
		 */
		public function save() {
			$has_error = false;
			// Get the current step.
			$step = Give_Settings_Import::get_step();

			// Validation for first step.
			if ( 1 === $step ) {
				$csv = (int) sanitize_text_field( $_POST['csv'] );
				if ( $csv ) {
					if ( ! wp_get_attachment_url( $csv ) ) {
						$has_error = true;
						Give_Admin_Settings::add_error( 'give-import-csv', __( 'Please upload or provide the ID to a valid CSV file.', 'give-manual-donations' ) );
					} elseif ( ( $mime_type = get_post_mime_type( $csv ) ) && ! strpos( $mime_type, 'csv' ) ) {
						$has_error = true;
						Give_Admin_Settings::add_error( 'give-import-csv', __( 'Please upload or provide the ID to a valid CSV file.', 'give-manual-donations' ) );
					}
				} else {
					$has_error = true;
					Give_Admin_Settings::add_error( 'give-import-csv', __( 'Please upload or provide the ID to a valid CSV file.', 'give-manual-donations' ) );
				}

				if ( false == $has_error ) {
					$url = give_import_page_url( array(
						'step'      => '2',
						'csv'       => $csv,
						'existing'  => ( isset( $_REQUEST['existing'] ) ) ? sanitize_text_field( $_REQUEST['existing'] ) : '',
						'delimiter' => ( isset( $_REQUEST['delimiter'] ) ) ? sanitize_text_field( $_REQUEST['delimiter'] ) : '',
					) );
					?>
                    <script type="text/javascript">
                        window.location = "<?php echo $url; ?>"
                    </script>
					<?php
				}
			}
		}

		/**
		 * Add this page to settings.
		 *
		 * @since  1.8
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
		 * @since  1.8
		 * @return array
		 */
		public function get_settings() {
			// Hide save button.
			$GLOBALS['give_hide_save_button'] = true;

			/**
			 * Filter the settings.
			 *
			 * @since  1.8
			 *
			 * @param  array $settings
			 */
			$settings = apply_filters(
				'give_get_settings_' . $this->id,
				array(
					array(
						'id'   => 'import',
						'name' => __( 'Import Donations', 'give-manual-donations' ),
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
		 * @since  1.8
		 * @return void
		 */
		public function output() {
			$settings = $this->get_settings();
			Give_Admin_Settings::output_fields( $settings, 'give_settings' );
		}

		/**
		 * Render report import field
		 *
		 * @since  1.8
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
