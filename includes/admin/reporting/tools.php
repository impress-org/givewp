<?php
/**
 * Tools
 *
 * @package     Give
 * @subpackage  Admin/Reports
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.5
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Display the recount stats tools
 *
 * @since       1.5
 * @return      void
 */
function give_tools_recount_stats_display() {

	if ( ! current_user_can( 'manage_give_settings' ) ) {
		return;
	}

	/**
	 * Fires before displaying the recount stats tools.
	 *
	 * @since 1.5
	 */
	do_action( 'give_tools_recount_stats_before' );
	?>
	<div id="poststuff">
		<div class="postbox">

			<h2 class="hndle ui-sortable-handle"><span><?php esc_html_e( 'Recount Stats', 'give' ); ?></span></h2>

			<div class="inside recount-stats-controls">
				<p><?php esc_html_e( 'Use these tools to recount stats, delete test transactions, or reset stats.', 'give' ); ?></p>
				<form method="post" id="give-tools-recount-form" class="give-export-form">

					<?php wp_nonce_field( 'give_ajax_export', 'give_ajax_export' ); ?>

					<select name="give-export-class" id="recount-stats-type">
						<option value="0" selected="selected" disabled="disabled"><?php esc_html_e( 'Please select an option', 'give' ); ?></option>
						<option data-type="recount-stats" value="Give_Tools_Recount_Income"><?php esc_html_e( 'Recalculate Total Donation Income Amount', 'give' ); ?></option>
						<option data-type="recount-form" value="Give_Tools_Recount_Form_Stats"><?php esc_html_e( 'Recalculate Income Amount and Donation Counts for a Form', 'give' ); ?></option>
						<option data-type="recount-all" value="Give_Tools_Recount_All_Stats"><?php esc_html_e( 'Recalculate Income Amount and Donation Counts for All Forms', 'give' ); ?></option>
						<option data-type="recount-customer-stats" value="Give_Tools_Recount_Customer_Stats"><?php esc_html_e( 'Recalculate Donor Statistics', 'give' ); ?></option>
						<option data-type="delete-test-transactions" value="Give_Tools_Delete_Test_Transactions"><?php esc_html_e( 'Delete Test Transactions', 'give' ); ?></option>
						<option data-type="reset-stats" value="Give_Tools_Reset_Stats"><?php esc_html_e( 'Delete All Data', 'give' ); ?></option>
						<?php
						/**
						 * Fires in the recount stats selectbox.
						 *
						 * Allows you to add new recount tool option elements.
						 *
						 * @since 1.5
						 */
						do_action( 'give_recount_tool_options' );
						?>
					</select>

					<span id="tools-form-dropdown" style="display: none">
						<?php
						$args = array(
							'name'   => 'form_id',
							'number' => - 1,
							'chosen' => true,
						);
						echo Give()->html->forms_dropdown( $args );
						?>
					</span>

					<input type="submit" id="recount-stats-submit" value="<?php esc_attr_e( 'Submit', 'give' ); ?>" class="button-secondary"/>

					<br/>

					<span class="give-recount-stats-descriptions">
						<span id="recount-stats"><?php esc_html_e( 'Recalculates the overall donation income amount.', 'give' ); ?></span>
						<span id="recount-form"><?php esc_html_e( 'Recalculates the donation and income stats for a specific form.', 'give' ); ?></span>
						<span id="recount-all"><?php esc_html_e( 'Recalculates the earnings and sales stats for all forms.', 'give' ); ?></span>
						<span id="recount-customer-stats"><?php esc_html_e( 'Recalculates the lifetime value and donation counts for all donors.', 'give' ); ?></span>
						<?php
						/**
						 * Fires in the recount stats description area.
						 *
						 * Allows you to add new recount tool description text.
						 *
						 * @since 1.5
						 */
						do_action( 'give_recount_tool_descriptions' );
						?>
						<span id="delete-test-transactions"><?php _e( '<strong>Deletes</strong> all TEST donations, donors, and related log entries.', 'give' ); ?></span>
						<span id="reset-stats"><?php _e( '<strong>Deletes</strong> ALL donations, donors, and related log entries regardless of test or live mode.', 'give' ); ?></span>
					</span>

					<span class="spinner"></span>

				</form>
				<?php
				/**
				 * Fires after the recount form.
				 *
				 * Allows you to add new elements after the form.
				 *
				 * @since 1.5
				 */
				do_action( 'give_tools_recount_forms' );
				?>
			</div><!-- .inside -->
		</div><!-- .postbox -->
	</div><!-- #poststuff -->
	<?php
	/**
	 * Fires after displaying the recount stats tools.
	 *
	 * @since 1.5
	 */
	do_action( 'give_tools_recount_stats_after' );
}

add_action( 'give_reports_tab_tools', 'give_tools_recount_stats_display' );
