<?php
/**
 * Core Settings Import Class
 *
 * This class handles core setting import.
 *
 * @package     Give
 * @subpackage  Classes/Give_Import_Core_Settings
 * @copyright   Copyright (c) 2017, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.8.17
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'Give_Import_Core_Settings' ) ) {

	/**
	 * Give_Import_Core_Settings.
	 *
	 * @since 1.8.17
	 */
	final class Give_Import_Core_Settings {

		/**
		 * Importer type
		 *
		 * @since 1.8.17
		 * @var string
		 */
		private $importer_type = 'import_core_setting';

		/**
		 * Instance.
		 *
		 * @since 1.8.17
		 */
		private static $instance;

		/**
		 * Importing donation per page.
		 *
		 * @since 1.8.17
		 *
		 * @var   int
		 */
		public static $per_page = 20;

		/**
		 * Is core file is valid.
		 *
		 * @since 2.1
		 *
		 * @var   int
		 */
		public $is_json_valid = false;

		/**
		 * Singleton pattern.
		 *
		 * @since 1.8.17
		 *
		 * @access private
		 */
		private function __construct() {
		}

		/**
		 * Get instance.
		 *
		 * @since 1.8.17
		 *
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
		 * @since 1.8.17
		 *
		 * @return void
		 */
		public function setup() {
			$this->setup_hooks();
		}


		/**
		 * Setup Hooks.
		 *
		 * @since 1.8.17
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
			add_action( 'give_tools_import_core_settings_form_start', array( $this, 'html' ), 10 );

			// Run when form submit.
			add_action( 'give-tools_save_import', array( $this, 'save' ) );

			add_action( 'give-tools_update_notices', array( $this, 'update_notices' ), 11, 1 );

			// Used to add submit button.
			add_action( 'give_tools_import_core_settings_form_end', array( $this, 'submit' ), 10 );
		}

		/**
		 * Update notice
		 *
		 * @since 1.8.17
		 *
		 * @param $messages
		 *
		 * @return mixed
		 */
		public function update_notices( $messages ) {
			if ( ! empty( $_GET['tab'] ) && 'import' === give_clean( $_GET['tab'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- read-only import step param; it only changes what is shown and saves nothing. give_clean() unslashes and sanitizes with sanitize_text_field().
				unset( $messages['give-setting-updated'] );
			}

			return $messages;
		}

		/**
		 * Print submit and nonce button.
		 *
		 * @since TBD Escape output.
		 * @since 1.8.17
		 */
		public function submit() {
			wp_nonce_field( 'give-save-settings', '_give-save-settings' );
			?>
			<input type="hidden" class="import-step" id="import-step" name="step" value="<?php echo (int) $this->get_step(); ?>"/>
			<input type="hidden" class="importer-type" value="<?php echo esc_attr( $this->importer_type ); ?>"/>
			<?php
		}

		/**
		 * Print the HTML for core setting importer.
		 *
		 * @since TBD Escape output.
		 * @since 1.8.17
		 */
		public function html() {
			$step = $this->get_step();

			// Show progress.
			$this->render_progress();
			?>
			<section>
				<table
					class="widefat export-options-table give-table <?php echo esc_attr( "step-{$step}" ); ?> <?php echo( 1 === $step && ! empty( $this->is_json_valid ) ? 'give-hidden' : '' ); ?> "
					id="<?php echo esc_attr( "step-{$step}" ); ?>">
					<tbody>
					<?php
					switch ( $step ) {
						case 1:
							$this->render_upload_html();
							break;

						case 2:
							$this->start_import();
							break;

						case 3:
							$this->import_success();
					}
					?>
					</tbody>
				</table>
			</section>
			<?php
		}

		/**
		 * Show message after the Core Settings Imported
		 *
		 * @since TBD Escape output. Verify a nonce before reverting the imported settings.
		 * @since 1.8.17
		 */
		public function import_success() {
			// Imported successfully

			$success           = (bool) ( isset( $_GET['success'] ) ? give_clean( $_GET['success'] ) : false ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- read-only result flag; it only picks which message and links are shown. give_clean() unslashes and sanitizes with sanitize_text_field().
			$undo              = (bool) ( isset( $_GET['undo'] ) ? give_clean( $_GET['undo'] ) : false ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- this flag only decides whether to check the nonce below; the settings are not reverted before check_admin_referer() passes. give_clean() unslashes and sanitizes with sanitize_text_field().
			$query_arg_setting = array(
				'post_type' => 'give_forms',
				'page'      => 'give-settings',
			);

			if ( $undo ) {
				check_admin_referer( 'give_core_settings_import_undo' );

				$success = false;
			}

			$query_arg_success = array(
				'post_type'     => 'give_forms',
				'page'          => 'give-tools',
				'tab'           => 'import',
				'importer-type' => 'import_core_setting',
				'step'          => '1',
				'undo'          => 'true',
			);

			$title = __( 'Settings Import Complete!', 'give' );
			if ( $success ) {
				$query_arg_success['undo']    = '1';
				$query_arg_success['step']    = '3';
				$query_arg_success['success'] = '1';
				$text                         = __( 'Undo Importing', 'give' );
			} else {
				if ( $undo ) {
					$host_give_options = get_option( 'give_settings_old', array() );
					update_option( 'give_settings', $host_give_options, false );
					$title = __( 'Successfully Reverted Settings Import', 'give' );
				} else {
					$title = __( 'Failed to Import', 'give' );
				}

				$text = __( 'Import Again', 'give' );
			}

			$result_url = add_query_arg( $query_arg_success, admin_url( 'edit.php' ) );

			// The "Undo Importing" link reverts the settings, so it carries a nonce.
			if ( $success ) {
				$result_url = wp_nonce_url( $result_url, 'give_core_settings_import_undo' );
			}
			?>
			<tr valign="top" class="give-import-dropdown">
				<th colspan="2">
					<h2><?php echo esc_html( $title ); ?></h2>
					<p>
						<a class="button button-large button-secondary" href="<?php echo esc_url( $result_url ); ?>"><?php echo esc_html( $text ); ?></a>
						<a class="button button-large button-secondary" href="<?php echo esc_url( add_query_arg( $query_arg_setting, admin_url( 'edit.php' ) ) ); ?>"><?php echo esc_html__( 'View Settings', 'give' ); ?></a>
					</p>
				</th>
			</tr>
			<?php
		}

		/**
		 * Will start Import
		 *
		 * @since 1.8.17
		 */
		public function start_import() {
			$type      = ( ! empty( $_GET['type'] ) ? give_clean( $_GET['type'] ) : 'replace' ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- read-only import step param; it only changes what is shown and saves nothing. give_clean() unslashes and sanitizes with sanitize_text_field().
			$file_name = ( ! empty( $_GET['file_name'] ) ? give_clean( $_GET['file_name'] ) : '' ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- read-only import step param; it only changes what is shown and saves nothing. give_clean() unslashes and sanitizes with sanitize_text_field().

			?>
			<tr valign="top" class="give-import-dropdown">
				<th colspan="2">
					<h2 id="give-import-title"><?php esc_html_e( 'Importing', 'give' ); ?></h2>
					<p class="give-field-description"><?php esc_html_e( 'Your settings are now being imported...', 'give' ); ?></p>
				</th>
			</tr>

			<tr valign="top" class="give-import-dropdown">
				<th colspan="2">
					<div class="give-progress">
						<div style="width: 50%"></div>
					</div>
					<span class="spinner is-active"></span>
					<input type="hidden" value="2" name="step">
					<input type="hidden" value="<?php echo esc_attr( $type ); ?>" name="type">
					<input type="hidden" value="<?php echo esc_attr( $file_name ); ?>" name="file_name">
				</th>
			</tr>
			<?php
		}

		/**
		 * Is used to show the process when user upload the donor form.
		 *
		 * @since 1.8.17
		 */
		public function render_progress() {
			$step = $this->get_step();
			?>
			<ol class="give-progress-steps">
				<li class="<?php echo( 1 === $step ? 'active' : '' ); ?>">
					<?php esc_html_e( 'Upload JSON file', 'give' ); ?>
				</li>
				<li class="<?php echo( 2 === $step ? 'active' : '' ); ?>">
					<?php esc_html_e( 'Import', 'give' ); ?>
				</li>
				<li class="<?php echo( 3 === $step ? 'active' : '' ); ?>">
					<?php esc_html_e( 'Done!', 'give' ); ?>
				</li>
			</ol>
			<?php
		}

		/**
		 * Will return the import step.
		 *
		 * @since 1.8.17
		 *
		 * @return int $step on which step doest the import is on.
		 */
		public function get_step() {
			$step    = (int) ( isset( $_REQUEST['step'] ) ? give_clean( $_REQUEST['step'] ) : 0 ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- read-only import step param; it only changes what is shown and saves nothing. give_clean() unslashes and sanitizes with sanitize_text_field().
			$on_step = 1;

			if ( empty( $step ) || 1 === $step ) {
				$on_step = 1;
			} elseif ( 2 === $step ) {
				$on_step = 2;
			} elseif ( 3 === $step ) {
				$on_step = 3;
			}

			return $on_step;
		}

		/**
		 * Render donations import page
		 *
		 * @since 1.8.17
		 */
		public function render_page() {
			include_once GIVE_PLUGIN_DIR . 'includes/admin/tools/views/html-admin-page-import-core-settings.php';
		}

		/**
		 * Add json upload HTMl
		 *
		 * Print the html of the file upload from which json will be uploaded.
		 *
		 * @since TBD Escape output.
		 * @since 1.8.17
		 * @return void
		 */
		public function render_upload_html() {
			$json = ( isset( $_POST['json'] ) ? give_clean( $_POST['json'] ) : '' ); // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- read-only import step param; it only changes what is shown and saves nothing. give_clean() unslashes and sanitizes with sanitize_text_field().
			$type = ( isset( $_POST['type'] ) ? give_clean( $_POST['type'] ) : 'merge' ); // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- read-only import step param; it only changes what is shown and saves nothing. give_clean() unslashes and sanitizes with sanitize_text_field().
			$step = $this->get_step();

			?>
			<tr valign="top">
				<th colspan="2">
					<h2 id="give-import-title"><?php esc_html_e( 'Import Core Settings from a JSON file', 'give' ); ?></h2>
					<p class="give-field-description"><?php esc_html_e( 'This tool allows you to import GiveWP settings from another GiveWP installation. Settings imported contain data from GiveWP core as well as any of our Premium Add-ons.', 'give' ); ?></p>
				</th>
			</tr>

			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="json"><?php esc_html_e( 'Choose a JSON file:', 'give' ); ?></label>
				</th>
				<td class="give-forminp">
					<div class="give-field-wrap">
						<label for="json">
							<input type="file" name="json" class="give-upload-json-file" value="<?php echo esc_attr($json); ?>"
								   accept=".json">
							<p class="give-field-description"><?php esc_html_e( 'The file type must be JSON.', 'give' ); ?></p>
						</label>
					</div>
				</td>
			</tr>
			<?php
			$settings = array(
				array(
					'id'          => 'type',
					'name'        => __( 'Merge Type:', 'give' ),
					'description' => __( 'Select "Merge" to retain existing settings, or "Replace" to overwrite with the settings from the JSON file', 'give' ),
					'default'     => $type,
					'type'        => 'radio_inline',
					'options'     => array(
						'merge'   => __( 'Merge', 'give' ),
						'replace' => __( 'Replace', 'give' ),
					),
				),
			);

			$settings = apply_filters( 'give_import_core_setting_html', $settings );

			if ( empty( $this->is_json_valid ) ) {
				Give_Admin_Settings::output_fields( $settings, 'give_settings' );
				?>
				<tr valign="top">
					<th></th>
					<th>
						<input type="submit"
							   class="button button-primary button-large button-secondary <?php echo esc_attr( "step-{$step}" ); ?>"
							   id="recount-stats-submit"
							   value="<?php esc_attr_e( 'Submit', 'give' ); ?>"/>
					</th>
				</tr>
				<?php
			} else {
				?>
				<input type="hidden" name="is_json_valid" class="is_json_valid" value="<?php echo esc_attr( $this->is_json_valid ); ?>">
				<?php
			}
		}

		/**
		 * Run when user click on the submit button.
		 *
		 * @since 1.8.17
		 */
		public function save() {

			// Get the current step.
			$step = $this->get_step();

			// Validation for first step.
			if ( 1 === $step ) {
				$type          = ( ! empty( $_REQUEST['type'] ) ? give_clean( $_REQUEST['type'] ) : 'replace' ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- nonce verified by Give_Admin_Settings::save() before it fires give-tools_save_import. give_clean() unslashes and sanitizes with sanitize_text_field().
				$core_settings = self::upload_widget_settings_file();
				if ( ! empty( $core_settings['error'] ) ) {
					Give_Admin_Settings::add_error( 'give-import-csv', __( 'Please upload a valid JSON settings file.', 'give' ) );
				} else {
					$file_path = explode( '/', $core_settings['file'] );
					$count     = ( count( $file_path ) - 1 );
					$url       = give_import_page_url(
						(array) apply_filters(
							'give_import_core_settings_importing_url',
							array(
								'step'          => '2',
								'importer-type' => $this->importer_type,
								'type'          => $type,
								'file_name'     => $file_path[ $count ],
							)
						)
					);

					$this->is_json_valid = $url;
				}
			}
		}

		/**
		 * Get if current page import donations page or not
		 *
		 * @since 1.8.17
		 * @return bool
		 */
		private function is_donations_import_page() {
			return 'import' === give_get_current_setting_tab() && isset( $_GET['importer-type'] ) && $this->importer_type === give_clean( $_GET['importer-type'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- read-only import step param; it only changes what is shown and saves nothing. give_clean() unslashes and sanitizes with sanitize_text_field().
		}

		/**
		 * Upload JSON file
		 *
		 * @return boolean
		 */
		public static function upload_widget_settings_file() {
			$upload = false;
			if ( isset( $_FILES['json'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce verified by Give_Admin_Settings::save() before give-tools_save_import fires; this runs from save().
				add_filter( 'upload_mimes', array( __CLASS__, 'json_upload_mimes' ) );
				add_filter( 'wp_check_filetype_and_ext', array( __CLASS__, 'filetype_mod' ), 10, 4 );

				$upload = wp_handle_upload( $_FILES['json'], array( 'test_form' => false ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce verified by Give_Admin_Settings::save() before give-tools_save_import fires; this runs from save().

				remove_filter( 'upload_mimes', array( __CLASS__, 'json_upload_mimes' ) );
				remove_filter( 'wp_check_filetype_and_ext', array( __CLASS__, 'filetype_mod' ) );

			} else {
				Give_Admin_Settings::add_error( 'give-import-csv', __( 'Please upload or provide a valid JSON file.', 'give' ) );
			}

			return $upload;
		}

		/**
		 * Add mime type for JSON
		 *
		 * @param array $existing_mimes
		 *
		 * @return array
		 */
		public static function json_upload_mimes( $existing_mimes = array() ) {

			$existing_mimes['json'] = 'application/json';
            $existing_mimes['text'] = 'text/plain';

			return $existing_mimes;
		}

		/**
		 * Allow for json file type uploads.
		 *
		 * https://github.com/impress-org/give/issues/3907
		 *
		 * @param $check
		 * @param $file
		 * @param $filename
		 * @param $mimes
		 *
		 * @return mixed
		 */
		public static function filetype_mod( $check, $file, $filename, $mimes ) {
			if ( empty( $check['ext'] ) && empty( $check['type'] ) ) {
				$finfo     = finfo_open( FILEINFO_MIME_TYPE );
				$real_mime = finfo_file( $finfo, $file );
				finfo_close( $finfo );

				if ( in_array( $real_mime, array( 'text/plain', 'text/html' ) ) ) {
					remove_filter( 'wp_check_filetype_and_ext', array( __CLASS__, 'filetype_mod' ) );

					// Allow JSON uploads
					$secondary_mime = array( 'json' => $real_mime );
					// Run another check, but only for our secondary mime and not on core mime types.
					$check = wp_check_filetype_and_ext( $file, $filename, $secondary_mime );

					add_filter( 'wp_check_filetype_and_ext', array( __CLASS__, 'filetype_mod' ), 10, 4 );
				}
			}

			return $check;
		}
	}

	Give_Import_Core_Settings::get_instance()->setup();
}
