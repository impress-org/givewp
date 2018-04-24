<?php
/**
 * Admin View: Exports
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} ?>

<div id="poststuff">
	<div id="give-dashboard-widgets-wrap">
		<div id="post-body">
			<div id="post-body-content">

				<?php
				/**
				 * Fires before the reports export tab.
				 *
				 * @since 1.0
				 */
				do_action( 'give_tools_tab_export_content_top' );
				?>

				<table class="widefat export-options-table give-table striped">
					<thead>
					<tr>
						<th scope="col"><?php esc_html_e( 'Export Type', 'give' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Export Options', 'give' ); ?></th>
					</tr>
					</thead>
					<tbody>
					<?php
					/**
					 * Fires in the reports export tab.
					 *
					 * Allows you to add new TR elements to the table before
					 * other elements.
					 *
					 * @since 1.0
					 */
					do_action( 'give_tools_tab_export_table_top' );
					?>

					<tr class="give-export-donations-history">
						<td scope="row" class="row-title">
							<h3>
								<span><?php esc_html_e( 'Export Donation History', 'give' ); ?></span>
							</h3>
							<p><?php esc_html_e( 'Download a CSV of all donations recorded.', 'give' ); ?></p>
						</td>
						<td>
							<a class="button" href="<?php echo esc_url( add_query_arg( array( 'type' => 'export_donations' ) ) ); ?>">
								<?php esc_html_e( 'Generate CSV', 'give' ); ?>
							</a>
						</td>
					</tr>

					<tr class="give-export-pdf-sales-earnings">
						<td scope="row" class="row-title">
							<h3>
								<span><?php esc_html_e( 'Export PDF of Donations and Income', 'give' ); ?></span>
							</h3>
							<p><?php esc_html_e( 'Download a PDF of Donations and Income reports for all forms for the current year.', 'give' ); ?></p>
						</td>
						<td>
							<a class="button" href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'give-action' => 'generate_pdf' ) ), 'give_generate_pdf' ) ); ?>">
								<?php esc_html_e( 'Generate PDF', 'give' ); ?>
							</a>
						</td>
					</tr>
					<tr class="give-export-sales-earnings">
						<td scope="row" class="row-title">
							<h3>
								<span><?php esc_html_e( 'Export Income and Donation Stats', 'give' ); ?></span>
							</h3>
							<p><?php esc_html_e( 'Download a CSV of income and donations over time.', 'give' ); ?></p>
						</td>
						<td>
							<form method="post">
								<?php
								echo wp_kses_post( sprintf(
									/* translators: 1: start date dropdown 2: end date dropdown */
									esc_html__( '%1$s to %2$s', 'give' ),
									Give()->html->year_dropdown( 'start_year' ) . ' ' . Give()->html->month_dropdown( 'start_month' ),
									Give()->html->year_dropdown( 'end_year' ) . ' ' . Give()->html->month_dropdown( 'end_month' )
								) );
								?>
								<input type="hidden" name="give-action" value="earnings_export"/>
								<input type="submit" value="<?php esc_attr_e( 'Generate CSV', 'give' ); ?>" class="button-secondary"/>
							</form>
						</td>
					</tr>

					<tr class="give-export-donors">
						<td scope="row" class="row-title">
							<h3>
								<span><?php esc_html_e( 'Export Donors in CSV', 'give' ); ?></span>
							</h3>
							<p><?php esc_html_e( 'Download an export of donors for all donation forms or only those who have given to a particular form.', 'give' ); ?></p>
						</td>
						<td>
							<form method="post" id="give_donor_export" class="give-export-form">
								<?php
								// Start Date form field for donors
								echo wp_kses_post( Give()->html->date_field( array(
									'id'          => 'give_donor_export_start_date',
									'name'        => 'donor_export_start_date',
									'placeholder' => esc_attr__( 'Start date', 'give' ),
								) ) );

								// End Date form field for donors
								echo wp_kses_post( Give()->html->date_field( array(
									'id'          => 'give_donor_export_end_date',
									'name'        => 'donor_export_end_date',
									'placeholder' => esc_attr__( 'End date', 'give' ),
								) ) );

								// Donation forms dropdown for donors export
								echo wp_kses_post( Give()->html->forms_dropdown( array(
									'name'   => 'forms',
									'id'     => 'give_donor_export_form',
									'chosen' => true,
								) ) );
								?>
								<input type="submit" value="<?php esc_attr_e( 'Generate CSV', 'give' ); ?>" class="button-secondary"/>

								<div id="export-donor-options-wrap" class="give-clearfix">
									<p><?php esc_html_e( 'Export Columns:', 'give' ); ?></p>
									<ul id="give-export-option-ul">
										<li>
											<label for="give-export-fullname">
												<input type="checkbox" checked name="give_export_option[full_name]" id="give-export-fullname"><?php esc_html_e( 'Name', 'give' ); ?>
											</label>
										</li>
										<li>
											<label for="give-export-email">
												<input type="checkbox" checked name="give_export_option[email]" id="give-export-email"><?php esc_html_e( 'Email', 'give' ); ?>
											</label>
										</li>
										<li>
											<label for="give-export-address">
												<input type="checkbox" checked name="give_export_option[address]" id="give-export-address"><?php esc_html_e( 'Address', 'give' ); ?>
											</label>
										</li>
										<li>
											<label for="give-export-userid">
												<input type="checkbox" checked name="give_export_option[userid]" id="give-export-userid"><?php esc_html_e( 'User ID', 'give' ); ?>
											</label>
										</li>
										<li>
											<label for="give-export-donation-form">
												<input type="checkbox" checked name="give_export_option[donation_form]" id="give-export-donation-form"><?php esc_html_e( 'Donation Form', 'give' ); ?>
											</label>
										</li>
										<li>
											<label for="give-export-first-donation-date">
												<input type="checkbox" checked name="give_export_option[date_first_donated]" id="give-export-first-donation-date"><?php esc_html_e( 'First Donation Date', 'give' ); ?>
											</label>
										</li>
										<li>
											<label for="give-export-donation-number">
												<input type="checkbox" checked name="give_export_option[donations]" id="give-export-donation-number"><?php esc_html_e( 'Number of Donations', 'give' ); ?>
											</label>
										</li>
										<li>
											<label for="give-export-donation-sum">
												<input type="checkbox" checked name="give_export_option[donation_sum]" id="give-export-donation-sum"><?php esc_html_e( 'Total Donated', 'give' ); ?>
											</label>
										</li>
									</ul>
								</div>
								<?php wp_nonce_field( 'give_ajax_export', 'give_ajax_export' ); ?>
								<input type="hidden" name="give-export-class" value="Give_Batch_Donors_Export"/>
								<input type="hidden" name="give_export_option[query_id]" value="<?php echo esc_attr( uniqid( 'give_' ) ); ?>"/>
							</form>
						</td>
					</tr>

					<tr class="give-export-core-settings">
						<td scope="row" class="row-title">
							<h3>
								<span><?php esc_html_e( 'Export Give Settings', 'give' ); ?></span>
							</h3>
							<p><?php esc_html_e( 'Download an export of Give\'s settings and import it in a new WordPress installation.', 'give' ); ?></p>
						</td>
						<td>
							<form method="post">
								<?php
								$export_excludes = apply_filters( 'settings_export_excludes', array() );
								if ( ! empty( $export_excludes ) ) {
									?>
									<i class="settings-excludes-title"><?php esc_html_e( 'Checked options from the list will not be exported.', 'give' ); ?></i>
									<ul class="settings-excludes-list">
										<?php foreach ( $export_excludes as $option_key => $option_label ) { ?>
											<li>
												<label for="settings_export_excludes[<?php echo esc_attr( $option_key ); ?>]">
													<input
														type="checkbox" checked
														name="settings_export_excludes[<?php echo esc_attr( $option_key ); ?>]"
														id="settings_export_excludes[<?php echo esc_attr( $option_key ); ?>]"><?php echo esc_html( $option_label ); ?>
												</label>
											</li>
										<?php } ?>
									</ul>
								<?php } ?>
								<input type="hidden" name="give-action" value="core_settings_export"/>
								<input type="submit" value="<?php esc_attr_e( 'Export JSON', 'give' ); ?>" class="button-secondary"/>
							</form>
						</td>
					</tr>
					<?php
					/**
					 * Fires in the reports export tab.
					 *
					 * Allows you to add new TR elements to the table after
					 * other elements.
					 *
					 * @since 1.0
					 */
					do_action( 'give_tools_tab_export_table_bottom' );
					?>
					</tbody>
				</table>

				<?php
				/**
				 * Fires after the reports export tab.
				 *
				 * @since 1.0
				 */
				do_action( 'give_tools_tab_export_content_bottom' );
				?>

			</div>
			<!-- .post-body-content -->
		</div>
		<!-- .post-body -->
	</div><!-- #give-dashboard-widgets-wrap -->
</div><!-- #poststuff -->
