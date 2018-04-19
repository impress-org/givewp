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
		 * Filter to modity the Taxonomy args
		 *
		 * @since 2.1
		 *
		 * @param $args args for Taxonomy
		 *
		 * @return int args for Taxonomy
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
							<h2 id="give-export-title"><?php _e( 'Export Donation History and Custom Fields to CSV', 'give' ) ?></h2>
							<p class="give-field-description"><?php _e( 'Download an export of donors for specific donation forms with the option to include custom fields.', 'give' ) ?></p>
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
								<div class="give-clearfix give-clearfix">
									<?php
									echo Give()->html->category_dropdown(
										'give_forms_categories[]',
										0,
										array(
											'id'              => 'give_forms_categories',
											'class'           => 'give_forms_categories',
											'chosen'          => true,
											'multiple'        => true,
											'selected'        => array(),
											'show_option_all' => false,
											'placeholder'     => __( 'Choose one or more from categories', 'give' ),
											'data'            => array( 'search-type' => 'categories' ),
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
								<div class="give-clearfix give-clearfix">
									<?php
									echo Give()->html->tags_dropdown(
										'give_forms_tags[]',
										0,
										array(
											'id'              => 'give_forms_tags',
											'class'           => 'give_forms_tags',
											'chosen'          => true,
											'multiple'        => true,
											'selected'        => array(),
											'show_option_all' => false,
											'placeholder'     => __( 'Choose one or more from tags', 'give' ),
											'data'            => array( 'search-type' => 'tags' ),
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
							<div class="give-clearfix give-clearfix">
								<?php
								$args = array(
									'name'        => 'forms',
									'id'          => 'give-payment-form-select',
									'chosen'      => true,
									'placeholder' => __( 'All Forms', 'give' ),
									'data'            => array( 'no-form' => __( 'No donation forms found', 'give' ), ),
								);
								echo Give()->html->forms_dropdown( $args );
								?>
							</div>
						</td>
					</tr>

					<tr>
						<td scope="row" class="row-title">
							<label for="give-payment-export-start"><?php _e( 'Filter by Date:', 'give' ); ?></label>
						</td>
						<td class="give-field-wrap">
							<div class="give-clearfix give-clearfix">
								<?php
								$args = array(
									'id'          => 'give-payment-export-start',
									'name'        => 'start',
									'placeholder' => __( 'Start date', 'give' ),
								);
								echo Give()->html->date_field( $args ); ?>
								<?php
								$args = array(
									'id'          => 'give-payment-export-end',
									'name'        => 'end',
									'placeholder' => __( 'End date', 'give' ),
								);
								echo Give()->html->date_field( $args ); ?>
							</div>
						</td>
					</tr>

					<tr>
						<td scope="row" class="row-title">
							<label
								for="give-export-donations-status"><?php _e( 'Filter by Status:', 'give' ); ?></label>
						</td>
						<td>
							<div class="give-clearfix give-clearfix">
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

					<tr>
						<td scope="row" class="row-title">
							<label><?php _e( 'Standard Columns:', 'give' ); ?></label>
						</td>
						<td>
							<div class="give-clearfix give-clearfix">
								<ul class="give-export-option-ul">
									<li>
										<label for="give-export-donation-id">
											<input type="checkbox" checked
											       name="give_give_donations_export_option[donation_id]"
											       id="give-export-donation-id"><?php _e( 'Donation ID', 'give' ); ?>
										</label>
									</li>

									<?php
									if ( give_is_setting_enabled( give_get_option( 'sequential-ordering_status', 'disabled' ) ) ) {
										?>
										<li>
											<label for="give-export-seq-id">
												<input type="checkbox" checked
												       name="give_give_donations_export_option[seq_id]"
												       id="give-export-seq-id"><?php _e( 'Donation Number', 'give' ); ?>
											</label>
										</li>
										<?php
									}
									?>

									<li>
										<label for="give-export-first-name">
											<input type="checkbox" checked
											       name="give_give_donations_export_option[first_name]"
											       id="give-export-first-name"><?php _e( 'Donor\'s First Name', 'give' ); ?>
										</label>
									</li>
									<li>
										<label for="give-export-last-name">
											<input type="checkbox" checked
											       name="give_give_donations_export_option[last_name]"
											       id="give-export-last-name"><?php _e( 'Donor\'s Last Name', 'give' ); ?>
										</label>
									</li>
									<li>
										<label for="give-export-email">
											<input type="checkbox" checked
											       name="give_give_donations_export_option[email]"
											       id="give-export-email"><?php _e( 'Donor\'s Email', 'give' ); ?>
										</label>
									</li>
									<li>
										<label for="give-export-company">
											<input type="checkbox" checked
											       name="give_give_donations_export_option[company]"
											       id="give-export-company"><?php _e( 'Company Name', 'give' ); ?>
										</label>
									</li>
									<li>
										<label for="give-export-address">
											<input type="checkbox" checked
											       name="give_give_donations_export_option[address]"
											       id="give-export-address"><?php _e( 'Donor\'s Billing Address', 'give' ); ?>
										</label>
									</li>
									<li>
										<label for="give-export-donation-sum">
											<input type="checkbox" checked
											       name="give_give_donations_export_option[donation_total]"
											       id="give-export-donation-sum"><?php _e( 'Donation Total', 'give' ); ?>
										</label>
									</li>
									<li>
										<label for="give-export-donation-currencies">
											<input type="checkbox" checked
											       name="give_give_donations_export_option[currencies]"
											       id="give-export-donation-currencies"><?php _e( 'Donation Currencies', 'give' ); ?>
										</label>
									</li>
									<li>
										<label for="give-export-donation-status">
											<input type="checkbox" checked
											       name="give_give_donations_export_option[donation_status]"
											       id="give-export-donation-status"><?php _e( 'Donation Status', 'give' ); ?>
										</label>
									</li>
									<li>
										<label for="give-export-payment-gateway">
											<input type="checkbox" checked
											       name="give_give_donations_export_option[payment_gateway]"
											       id="give-export-payment-gateway"><?php _e( 'Payment Gateway', 'give' ); ?>
										</label>
									</li>
									<li>
										<label for="give-export-donation-form-id">
											<input type="checkbox" checked
											       name="give_give_donations_export_option[form_id]"
											       id="give-export-donation-form-id"><?php _e( 'Donation Form ID', 'give' ); ?>
										</label>
									</li>
									<li>
										<label for="give-export-donation-form-title">
											<input type="checkbox" checked
											       name="give_give_donations_export_option[form_title]"
											       id="give-export-donation-form-title"><?php _e( 'Donation Form Title', 'give' ); ?>
										</label>
									</li>
									<li>
										<label for="give-export-donation-form-level-id">
											<input type="checkbox" checked
											       name="give_give_donations_export_option[form_level_id]"
											       id="give-export-donation-form-level-id"><?php _e( 'Donation Form Level ID', 'give' ); ?>
										</label>
									</li>
									<li>
										<label for="give-export-donation-form-level-title">
											<input type="checkbox" checked
											       name="give_give_donations_export_option[form_level_title]"
											       id="give-export-donation-form-level-title"><?php _e( 'Donation Form Level Title', 'give' ); ?>
										</label>
									</li>
									<li>
										<label for="give-export-donation-date">
											<input type="checkbox" checked
											       name="give_give_donations_export_option[donation_date]"
											       id="give-export-donation-date"><?php _e( 'Donation Date', 'give' ); ?>
										</label>
									</li>
									<li>
										<label for="give-export-donation-time">
											<input type="checkbox" checked
											       name="give_give_donations_export_option[donation_time]"
											       id="give-export-donation-time"><?php _e( 'Donation Time', 'give' ); ?>
										</label>
									</li>

									<li>
										<label for="give-export-userid">
											<input type="checkbox" checked
											       name="give_give_donations_export_option[userid]"
											       id="give-export-userid"><?php _e( 'User ID', 'give' ); ?>
										</label>
									</li>
									<li>
										<label for="give-export-donorid">
											<input type="checkbox" checked
											       name="give_give_donations_export_option[donorid]"
											       id="give-export-donorid"><?php _e( 'Donor ID', 'give' ); ?>
										</label>
									</li>
									<li>
										<label for="give-export-donor-ip">
											<input type="checkbox" checked
											       name="give_give_donations_export_option[donor_ip]"
											       id="give-export-donor-ip"><?php _e( 'Donor IP Address', 'give' ); ?>
										</label>
									</li>
								</ul>
							</div>
						</td>
					</tr>

					<tr class="give-hidden give-export-donations-hide give-export-donations-ffm">
						<td scope="row" class="row-title">
							<label><?php _e( 'Form Field Manager Fields:', 'give' ); ?></label>
						</td>
						<td class="give-field-wrap">
							<div class="give-clearfix give-clearfix">
								<ul class="give-export-option-ul"></ul>
								<p class="give-field-description"><?php _e( 'The following fields have been created by Form Field Manager.', 'give' ); ?></p>
							</div>
						</td>
					</tr>

					<tr
						class="give-hidden give-export-donations-hide give-export-donations-standard-fields">
						<td scope="row" class="row-title">
							<label><?php _e( 'Custom Field Columns:', 'give' ); ?></label>
						</td>
						<td class="give-field-wrap">
							<div class="give-clearfix give-clearfix">
								<ul class="give-export-option-ul"></ul>
								<p class="give-field-description"><?php _e( 'The following fields may have been created by custom code, or another plugin.', 'give' ); ?></p>
							</div>
						</td>
					</tr>

					<tr class="give-hidden give-export-donations-hide give-export-donations-hidden-fields">
						<td scope="row" class="row-title">
							<label><?php _e( 'Hidden Custom Field Columns:', 'give' ); ?></label>
						</td>
						<td class="give-field-wrap">
							<div class="give-clearfix give-clearfix">
								<ul class="give-export-option-ul"></ul>
								<p class="give-field-description"><?php _e( 'The following hidden custom fields contain data created by Give Core, a Give Add-on, another plugin, etc.<br/>Hidden fields are generally used for programming logic, but you may contain data you would like to export.', 'give' ); ?></p>
							</div>
						</td>
					</tr>

					<tr class="end">
						<td>
						</td>
						<td>
							<?php wp_nonce_field( 'give_ajax_export', 'give_ajax_export' ); ?>
							<input type="hidden" name="give-export-class" value="Give_Export_Donations_CSV"/>
							<input type="submit" value="<?php _e( 'Generate CSV', 'give' ) ?>" class="give-export-donation-button button button-primary">
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
			<div id="poststuff">
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
