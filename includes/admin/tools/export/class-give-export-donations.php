<?php
/**
 * Give Export Donations Settings
 *
 * @package     Give
 * @subpackage  Classes/Give_Settings_Data
 * @copyright   Copyright (c) 2016, GiveWP
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Give_Export_Donations' ) ) {

	/**
	 * Class Give_Export_Donations
	 *
	 * @sine 2.1
	 */
	final class Give_Export_Donations {

		/**
		 * Importer type
		 *
		 * @since 2.1
		 *
		 * @var string
		 */
		private $exporter_type = 'export_donations';

		/**
		 * Instance.
		 *
		 * @since 2.1
		 */
		static private $instance;

		/**
		 * Singleton pattern.
		 *
		 * @since 2.1
		 *
		 * @access private
		 */
		private function __construct() {
		}

		/**
		 * Get instance.
		 *
		 * @since 2.1
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
		 * @since 2.1
		 *
		 * @return void
		 */
		public function setup() {
			$this->setup_hooks();
		}


		/**
		 * Setup Hooks.
		 *
		 * @since 2.1
		 *
		 * @return void
		 */
		private function setup_hooks() {
			if ( ! $this->is_donations_export_page() ) {
				return;
			}

			// Do not render main export tools page.
			remove_action(
				'give_admin_field_tools_export',
				array(
					'Give_Settings_Export',
					'render_export_field',
				),
				10
			);

			// Render donation export page
			add_action( 'give_admin_field_tools_export', array( $this, 'render_page' ) );

			// Print the HTML.
			add_action( 'give_tools_export_donations_form_start', array( $this, 'html' ) );
		}

		/**
		 * Filter to modity the Taxonomy args
		 *
		 * @since 2.1
		 *
		 * @param array $args args for Taxonomy
		 *
		 * @return array args for Taxonomy
		 */
		function give_forms_taxonomy_dropdown( $args ) {
			$args['number'] = 30;

			return $args;
		}

		/**
		 * Print the HTML for core setting exporter.
		 *
		 * @since 2.1
		 */
		public function html() {
			?>
			<section id="give-export-donations">
				<table class="widefat export-options-table give-table">
					<tbody>
					<tr class="top">
						<td colspan="2">
							<h2 id="give-export-title"><?php _e( 'Export Donation History and Custom Fields to CSV', 'give' ); ?></h2>
							<p class="give-field-description"><?php _e( 'Download an export of donors for specific donation forms with the option to include custom fields.', 'give' ); ?></p>
						</td>
					</tr>

					<?php
					if ( give_is_setting_enabled( give_get_option( 'categories' ) ) ) {
						add_filter( 'give_forms_category_dropdown', array( $this, 'give_forms_taxonomy_dropdown' ) );
						?>
						<tr>
							<td scope="row" class="row-title">
								<label
									for="give_forms_categories"><?php _e( 'Filter by Categories:', 'give' ); ?></label>
							</td>
							<td class="give-field-wrap">
								<div class="give-clearfix">
									<?php
									echo Give()->html->category_dropdown(
										'give_forms_categories[]',
										0,
										array(
											'id'          => 'give_forms_categories',
											'class'       => 'give_forms_categories',
											'chosen'      => true,
											'multiple'    => true,
											'selected'    => array(),
											'show_option_all' => false,
											'placeholder' => __( 'Choose one or more from categories', 'give' ),
											'data'        => array( 'search-type' => 'categories' ),
										)
									);
									?>
								</div>
							</td>
						</tr>
						<?php
						remove_filter( 'give_forms_category_dropdown', array( $this, 'give_forms_taxonomy_dropdown' ) );
					}

					if ( give_is_setting_enabled( give_get_option( 'tags' ) ) ) {
						add_filter( 'give_forms_tag_dropdown', array( $this, 'give_forms_taxonomy_dropdown' ) );
						?>
						<tr>
							<td scope="row" class="row-title">
								<label
									for="give_forms_tags"><?php _e( 'Filter by Tags:', 'give' ); ?></label>
							</td>
							<td class="give-field-wrap">
								<div class="give-clearfix">
									<?php
									echo Give()->html->tags_dropdown(
										'give_forms_tags[]',
										0,
										array(
											'id'          => 'give_forms_tags',
											'class'       => 'give_forms_tags',
											'chosen'      => true,
											'multiple'    => true,
											'selected'    => array(),
											'show_option_all' => false,
											'placeholder' => __( 'Choose one or more from tags', 'give' ),
											'data'        => array( 'search-type' => 'tags' ),
										)
									);
									?>
								</div>
							</td>
						</tr>
						<?php
						remove_filter( 'give_forms_tag_dropdown', array( $this, 'give_forms_taxonomy_dropdown' ) );
					}
					?>

					<tr class="give-export-donation-form">
						<td scope="row" class="row-title">
							<label
								for="give_payment_form_select"><?php _e( 'Filter by Donation Form:', 'give' ); ?></label>
						</td>
						<td class="give-field-wrap">
							<div class="give-clearfix">
								<?php
								$args = array(
									'name'        => 'forms',
									'id'          => 'give-payment-form-select',
									'class'       => 'give-width-25em',
									'chosen'      => true,
									'placeholder' => __( 'All Forms', 'give' ),
									'data'        => array( 'no-form' => __( 'No donation forms found', 'give' ) ),
								);
								echo Give()->html->forms_dropdown( $args );
								?>

								<input type="hidden" name="form_ids" class="form_ids" />
							</div>
						</td>
					</tr>

					<tr>
						<td scope="row" class="row-title">
							<label for="give-payment-export-start"><?php _e( 'Filter by Date:', 'give' ); ?></label>
						</td>
						<td class="give-field-wrap">
							<div class="give-clearfix">
								<?php
								$args = array(
									'id'           => 'give-payment-export-start',
									'name'         => 'start',
									'placeholder'  => __( 'Start Date', 'give' ),
									'autocomplete' => 'off',
								);
								echo Give()->html->date_field( $args );
								?>
								<?php
								$args = array(
									'id'           => 'give-payment-export-end',
									'name'         => 'end',
									'placeholder'  => __( 'End Date', 'give' ),
									'autocomplete' => 'off'
								);
								echo Give()->html->date_field( $args );
								?>
							</div>
						</td>
					</tr>

					<tr>
						<td scope="row" class="row-title">
							<label
								for="give-export-donations-status"><?php _e( 'Filter by Status:', 'give' ); ?></label>
						</td>
						<td>
							<div class="give-clearfix">
								<select name="status" id="give-export-donations-status">
									<option value="any"><?php _e( 'All Statuses', 'give' ); ?></option>
									<?php
									$statuses = give_get_payment_statuses();
									foreach ( $statuses as $status => $label ) {
										echo '<option value="' . $status . '">' . $label . '</option>';
									}
									?>
								</select>
							</div>
						</td>
					</tr>

					<?php
					/**
					 * Add fields columns that are going to be exported when exporting donations
					 *
					 * @since 2.1
					 */
					do_action( 'give_export_donation_fields' );
					?>

					<tr class="end">
						<td>
						</td>
						<td>
							<?php wp_nonce_field( 'give_ajax_export', 'give_ajax_export' ); ?>
							<input type="hidden" name="give-export-class" value="Give_Export_Donations_CSV"/>
							<input type="button" value="<?php esc_attr_e( 'Deselect All Fields', 'give' ); ?>" data-value="<?php esc_attr_e( 'Select All Fields', 'give' ); ?>" class="give-toggle-checkbox-selection button button-secondary">
							<input type="submit" value="<?php esc_attr_e( 'Generate CSV', 'give' ); ?>" class="give-export-donation-button button button-primary">
							<div class="add-notices"></div>
						</td>
					</tr>
					</tbody>
				</table>
			</section>
			<?php
		}

		/**
		 * Render donations export page
		 *
		 * @since 2.1
		 */
		public function render_page() {
			/**
			 * Fires before displaying the export div tools.
			 *
			 * @since 2.1
			 */
			do_action( 'give_tools_export_donations_main_before' );
			?>
			<div id="poststuff" class="give-clearfix">
				<div class="postbox">
					<h1 class="give-export-h1" align="center"><?php _e( 'Export Donations', 'give' ); ?></h1>
					<div class="inside give-tools-setting-page-export give-export_donations">
						<?php
						/**
						 * Fires before from start.
						 *
						 * @since 2.1
						 */
						do_action( 'give_tools_export_donations_form_before_start' );
						?>
						<form method="post"
						      id="give-export_donations-form"
							  class="give-export-form tools-setting-page-export tools-setting-page-export"
							  enctype="multipart/form-data">

							<?php
							/**
							 * Fires just after form start.
							 *
							 * @since 2.1
							 */
							do_action( 'give_tools_export_donations_form_start' );
							?>

							<?php
							/**
							 * Fires just after before form end.
							 *
							 * @since 2.1
							 */
							do_action( 'give_tools_export_donations_form_end' );
							?>
						</form>
						<?php
						/**
						 * Fires just after form end.
						 *
						 * @since 2.1
						 */
						do_action( 'give_tools_export_donations_form_after_end' );
						?>
					</div><!-- .inside -->
				</div><!-- .postbox -->
			</div><!-- #poststuff -->
			<?php
			/**
			 * Fires after displaying the export div tools.
			 *
			 * @since 2.1
			 */
			do_action( 'give_tools_export_donations_main_after' );
		}

		/**
		 * Get if current page export donations page or not
		 *
		 * @since 2.1
		 *
		 * @return bool
		 */
		private function is_donations_export_page() {
			return 'export' === give_get_current_setting_tab() && isset( $_GET['type'] ) && $this->exporter_type === give_clean( $_GET['type'] );
		}
	}

	Give_Export_Donations::get_instance()->setup();
}
