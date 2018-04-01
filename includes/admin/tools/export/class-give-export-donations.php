<?php
/**
 * Give Export Donations Settings
 *
 * @package     Give
 * @subpackage  Classes/Give_Settings_Data
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'Give_Export_Donations' ) ) {

	/**
	 * Give_Settings_CSV.
	 *
	 * @sine 2.1
	 */
	final class Give_Export_Donations {
		/**
		 * Importer type
		 *
		 * @since 2.1
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
			remove_action( 'give_admin_field_tools_export', array(
				'Give_Settings_Export',
				'render_export_field'
			), 10 );

			// Render donation export page
			add_action( 'give_admin_field_tools_export', array( $this, 'render_page' ) );

			// Print the HTML.
			add_action( 'give_tools_export_donations_form_start', array( $this, 'html' ) );
		}

		/**
		 * Print the HTML for core setting exporter.
		 *
		 * @since 2.1
		 */
		public function html() {
			?>
			<section>
				<table class="widefat export-options-table give-table">
					<tbody>
					<tr valign="top">
						<th colspan="2">
							<h2 id="give-export-title"><?php esc_html_e( 'Export Donation History and Custom Fields to CSV', 'give' ) ?></h2>
							<p class="give-field-description"><?php esc_html_e( 'Download an export of donors for specific donation forms with the option to include custom fields.', 'give' ) ?></p>
						</th>
					</tr>

					<tr valign="top">
						<th scope="row" class="titledesc">
							<label for="json"><?php _e( 'Select a Donation Form:', 'give' ); ?></label>
						</th>
						<td class="give-forminp">
							<div class="give-field-wrap">
								<label for="json">
									<?php
									$args = array(
										'name'        => 'forms',
										'id'          => 'give-form-for-csv-export',
										'chosen'      => true,
										'number'      => - 1,
										'placeholder' => esc_attr__( 'Select a Donation Form', 'give' ),
									);
									echo Give()->html->forms_dropdown( $args );
									?>
								</label>
							</div>
						</td>
					</tr>

					<tr valign="top">
						<th scope="row" class="titledesc">
							<label for="json"><?php _e( 'Filter by Date:', 'give' ); ?></label>
						</th>
						<td class="give-forminp">
							<div class="give-field-wrap">
								<label for="json">
									<?php
									$args = array(
										'id'          => 'give-payment-export-start',
										'name'        => 'start',
										'placeholder' => esc_attr__( 'Start date', 'give' ),
									);
									echo Give()->html->date_field( $args ); ?>
									<?php
									$args = array(
										'id'          => 'give-payment-export-end',
										'name'        => 'end',
										'placeholder' => esc_attr__( 'End date', 'give' ),
									);
									echo Give()->html->date_field( $args ); ?>
								</label>
							</div>
						</td>
					</tr>

					<tr valign="top">
						<th scope="row" class="titledesc">
							<label for="json"><?php _e( 'Filter by Status:', 'give' ); ?></label>
						</th>
						<td class="give-forminp">
							<div class="give-field-wrap">
								<label for="json">
									<select name="status">
										<option value="any"><?php esc_html_e( 'All Statuses', 'give' ); ?></option>
										<?php
										$statuses = give_get_payment_statuses();
										foreach ( $statuses as $status => $label ) {
											echo '<option value="' . $status . '">' . $label . '</option>';
										}
										?>
									</select>
								</label>
							</div>
						</td>
					</tr>

					<tr valign="top">
						<th scope="row" class="titledesc">
							<label for="json"><?php _e( 'Standard Columns:', 'give' ); ?></label>
						</th>
						<td class="give-forminp">
							<div class="give-field-wrap">
								<label for="json">
									<ul id="give-export-option-ul">
										<li>
											<label for="give-export-donation-id">
												<input type="checkbox" checked
												       name="give_give_donations_export_option[donation_id]"
												       id="give-export-donation-id"><?php esc_html_e( 'Donation ID', 'give' ); ?>
											</label>
										</li>
										<li>
											<label for="give-export-first-name">
												<input type="checkbox" checked
												       name="give_give_donations_export_option[first_name]"
												       id="give-export-first-name"><?php esc_html_e( 'Donor\'s First Name', 'give' ); ?>
											</label>
										</li>
										<li>
											<label for="give-export-last-name">
												<input type="checkbox" checked
												       name="give_give_donations_export_option[last_name]"
												       id="give-export-last-name"><?php esc_html_e( 'Donor\'s Last Name', 'give' ); ?>
											</label>
										</li>
										<li>
											<label for="give-export-email">
												<input type="checkbox" checked
												       name="give_give_donations_export_option[email]"
												       id="give-export-email"><?php esc_html_e( 'Donor\'s Email', 'give' ); ?>
											</label>
										</li>
										<li>
											<label for="give-export-address">
												<input type="checkbox" checked
												       name="give_give_donations_export_option[address]"
												       id="give-export-address"><?php esc_html_e( 'Donor\'s Billing Address', 'give' ); ?>
											</label>
										</li>
										<li>
											<label for="give-export-donation-sum">
												<input type="checkbox" checked
												       name="give_give_donations_export_option[donation_total]"
												       id="give-export-donation-sum"><?php esc_html_e( 'Donation Total', 'give' ); ?>
											</label>
										</li>
										<li>
											<label for="give-export-donation-status">
												<input type="checkbox" checked
												       name="give_give_donations_export_option[donation_status]"
												       id="give-export-donation-status"><?php esc_html_e( 'Donation Status', 'give' ); ?>
											</label>
										</li>
										<li>
											<label for="give-export-payment-gateway">
												<input type="checkbox" checked
												       name="give_give_donations_export_option[payment_gateway]"
												       id="give-export-payment-gateway"><?php esc_html_e( 'Payment Gateway', 'give' ); ?>
											</label>
										</li>
										<li>
											<label for="give-export-donation-form-id">
												<input type="checkbox" checked
												       name="give_give_donations_export_option[form_id]"
												       id="give-export-donation-form-id"><?php esc_html_e( 'Donation Form ID', 'give' ); ?>
											</label>
										</li>
										<li>
											<label for="give-export-donation-form-title">
												<input type="checkbox" checked
												       name="give_give_donations_export_option[form_title]"
												       id="give-export-donation-form-title"><?php esc_html_e( 'Donation Form Title', 'give' ); ?>
											</label>
										</li>
										<li>
											<label for="give-export-donation-form-level-id">
												<input type="checkbox" checked
												       name="give_give_donations_export_option[form_level_id]"
												       id="give-export-donation-form-level-id"><?php esc_html_e( 'Donation Form Level ID', 'give' ); ?>
											</label>
										</li>
										<li>
											<label for="give-export-donation-form-level-title">
												<input type="checkbox" checked
												       name="give_give_donations_export_option[form_level_title]"
												       id="give-export-donation-form-level-title"><?php esc_html_e( 'Donation Form Level Title', 'give' ); ?>
											</label>
										</li>
										<li>
											<label for="give-export-donation-date">
												<input type="checkbox" checked
												       name="give_give_donations_export_option[donation_date]"
												       id="give-export-donation-date"><?php esc_html_e( 'Donation Date', 'give' ); ?>
											</label>
										</li>
										<li>
											<label for="give-export-donation-time">
												<input type="checkbox" checked
												       name="give_give_donations_export_option[donation_time]"
												       id="give-export-donation-time"><?php esc_html_e( 'Donation Time', 'give' ); ?>
											</label>
										</li>

										<li>
											<label for="give-export-userid">
												<input type="checkbox" checked
												       name="give_give_donations_export_option[userid]"
												       id="give-export-userid"><?php esc_html_e( 'User ID', 'give' ); ?>
											</label>
										</li>
										<li>
											<label for="give-export-donorid">
												<input type="checkbox" checked
												       name="give_give_donations_export_option[donorid]"
												       id="give-export-donorid"><?php esc_html_e( 'Donor ID', 'give' ); ?>
											</label>
										</li>
										<li>
											<label for="give-export-donor-ip">
												<input type="checkbox" checked
												       name="give_give_donations_export_option[donor_ip]"
												       id="give-export-donor-ip"><?php esc_html_e( 'Donor IP Address', 'give' ); ?>
											</label>
										</li>
									</ul>
								</label>
							</div>
						</td>
					</tr>

					<tr valign="top" class="give-hidden give-export-donations-hide give-export-donations-ffm">
						<th scope="row" class="titledesc">
							<label for="json"><?php _e( 'Form Field Manager Fields:', 'give' ); ?></label>
						</th>
						<td class="give-forminp">
							<div class="give-field-wrap">
								<label for="json">
									<ul class=""></ul>
									<p class="give-field-description"><?php _e( 'The following fields have been created by Form Field Manager.', 'give' );?></p>
								</label>
							</div>
						</td>
					</tr>

					<tr valign="top" class="give-hidden give-export-donations-hide give-export-donations-standard-fields">
						<th scope="row" class="titledesc">
							<label for="json"><?php _e( 'Custom Field Columns:', 'give' ); ?></label>
						</th>
						<td class="give-forminp">
							<div class="give-field-wrap">
								<label for="json">
									<ul class=""></ul>
									<p class="give-field-description"><?php _e( 'The following fields may have been created by custom code, or another plugin.', 'give' );?></p>
								</label>
							</div>
						</td>
					</tr>

					<tr valign="top" class="give-hidden give-export-donations-hide give-export-donations-hidden-fields">
						<th scope="row" class="titledesc">
							<label for="json"><?php _e( 'Hidden Custom Field Columns:', 'give' ); ?></label>
						</th>
						<td class="give-forminp">
							<div class="give-field-wrap">
								<label for="json">
									<ul class=""></ul>
									<p class="give-field-description"><?php _e( 'The following hidden custom fields contain data created by Give Core, a Give Add-on, another plugin, etc. Hidden fields are generally used for programming logic, but you may contain data you would like to export.', 'give' );?></p>
								</label>
							</div>
						</td>
					</tr>

					<tr valign="top">
						<th></th>
						<th>
							<?php wp_nonce_field( 'give_ajax_export', 'give_ajax_export' ); ?>
							<input type="hidden" name="give-export-class" value="Give_give_donations_Donations_Export"/>
							<input type="submit" value="Generate CSV" class="button button-primary">
						</th>
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
			<div id="poststuff">
				<div class="postbox">
					<h1 class="give-export-h1" align="center"><?php esc_html_e( 'Export Donations', 'give' ); ?></h1>
					<div class="inside give-tools-setting-page-export give-export_donations">
						<?php
						/**
						 * Fires before from start.
						 *
						 * @since 2.1
						 */
						do_action( 'give_tools_export_donations_form_before_start' );
						?>
						<form method="post" id="give-export_donations-form"
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
						do_action( 'give_tools_iexport_donations_form_after_end' );
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
		 * @return bool
		 */
		private function is_donations_export_page() {
			return 'export' === give_get_current_setting_tab() && isset( $_GET['type'] ) && $this->exporter_type === give_clean( $_GET['type'] );
		}
	}

	Give_Export_Donations::get_instance()->setup();
}
