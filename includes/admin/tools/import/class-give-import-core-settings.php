<?php
/**
 * Core Settings Import Class
 *
 * This class handles core setting import.
 *
 * @package     Give
 * @subpackage  Classes/Give_Import_Core_Settings
 * @copyright   Copyright (c) 2017, WordImpress
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.8.16
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'Give_Import_Core_Settings' ) ) {

	/**
	 * Give_Import_Core_Settings.
	 *
	 * @since 1.8.16
	 */
	final class Give_Import_Core_Settings {

		/**
		 * Importer type
		 *
		 * @since 1.8.16
		 * @var string
		 */
		private $importer_type = 'import_core_setting';

		/**
		 * Instance.
		 *
		 * @since
		 * @access private
		 * @var
		 */
		static private $instance;

		/**
		 * Singleton pattern.
		 *
		 * @since
		 * @access private
		 */
		private function __construct() {
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
		 * @since 1.8.16
		 *
		 * @return void
		 */
		public function setup() {
			$this->setup_hooks();
		}


		/**
		 * Setup Hooks.
		 *
		 * @since 1.8.16
		 *
		 * @return void
		 */
		private function setup_hooks() {
			if ( ! $this->is_donations_import_page() ) {
				return;
			}

			// Do not render main import tools page.
			remove_action( 'give_admin_field_tools_import', array( 'Give_Settings_Import', 'render_import_field', ) );

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
		 * @since 1.8.16
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
		 * @since 1.8.16
		 */
		public function submit() {
			wp_nonce_field( 'give-save-settings', '_give-save-settings' );
			?>
			<input type="hidden" class="import-step" id="import-step" name="step"
			       value="<?php echo $this->get_step(); ?>"/>
			<input type="hidden" class="importer-type" value="<?php echo $this->importer_type; ?>"/>
			<?php
		}

		/**
		 * Print the HTML for importer.
		 *
		 * @since 1.8.16
		 */
		public function html() {
			$step = $this->get_step();

			// Show progress.
			$this->render_progress();
			?>
			<section>
				<table class="widefat export-options-table give-table <?php echo "step-{$step}"; ?>"
				       id="<?php echo "step-{$step}"; ?>">
					<tbody>
					<?php
					switch ( $this->get_step() ) {
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
		 * Show success notice
		 *
		 * @since 1.8.16
		 */
		public function import_success() {
			// Imported successfully
		}

		/**
		 * Will start Import
		 *
		 * @since 1.8.16
		 */
		public function start_import() {
			// Start Importing
		}

		/**
		 * Is used to show the process when user upload the donor form.
		 *
		 * @since 1.8.16
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
		 * @since 1.8.16
		 *
		 * @return int $step on which step doest the import is on.
		 */
		public function get_step() {
			$step    = (int) ( isset( $_REQUEST['step'] ) ? give_clean( $_REQUEST['step'] ) : 0 );
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
		 * @since 1.8.16
		 */
		public function render_page() {
			include_once GIVE_PLUGIN_DIR . 'includes/admin/tools/views/html-admin-page-import-core-settings.php';
		}

		/**
		 * Add json upload HTMl
		 *
		 * Print the html of the file upload from which json will be uploaded.
		 *
		 * @since 1.8.16
		 * @return void
		 */
		public function render_upload_html() {
			$json = ( isset( $_POST['json'] ) ? give_clean( $_POST['json'] ) : '' );
			$type = ( isset( $_POST['type'] ) ? give_clean( $_POST['type'] ) : 'merge' );

			?>
			<tr valign="top">
				<th colspan="2">
					<h2 id="give-import-title"><?php esc_html_e( 'Import Core Settings from a JSON file', 'give' ) ?></h2>
					<p class="give-field-description"><?php esc_html_e( 'This tool allows you to merge or replace core settings data to your give settings via a JSON file.', 'give' ) ?></p>
				</th>
			</tr>

			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="json">Choose a json file:</label>
				</th>
				<td class="give-forminp">
					<div class="give-field-wrap">
						<label for="json">
							<input type="file" name="json" class="give-upload-json-file" value="<?php echo $json; ?>"
							       accept=".json">
							<p class="give-field-description">The file must be a JSON file type only.</p>
						</label>
					</div>
				</td>
			</tr>
			<?php
			$settings = array(
				array(
					'id'          => 'type',
					'name'        => __( 'Merge Type:', 'give' ),
					'description' => __( 'Import the Core Setting from the JSON and then merge or replace with the current settings', 'give' ),
					'default'     => $type,
					'type'        => 'radio_inline',
					'options'     => array(
						'merge'   => __( 'Merge', 'give' ),
						'replace' => __( 'Replace', 'give' ),
					),
				),
			);

			$settings = apply_filters( 'give_import_core_setting_html', $settings );

			Give_Admin_Settings::output_fields( $settings, 'give_settings' );
		}

		/**
		 * Run when user click on the submit button.
		 *
		 * @since 1.8.16
		 */
		public function save() {
			// Check condition on save
		}

		/**
		 * Get if current page import donations page or not
		 *
		 * @since 1.8.16
		 * @return bool
		 */
		private function is_donations_import_page() {
			return 'import' === give_get_current_setting_tab() &&
			       isset( $_GET['importer-type'] ) &&
			       $this->importer_type === give_clean( $_GET['importer-type'] );
		}

	}

	Give_Import_Core_Settings::get_instance()->setup();
}
