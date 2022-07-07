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
							<a class="button" href="<?php echo esc_url( add_query_arg( [ 'type' => 'export_donations' ] ) ); ?>">
								<?php esc_html_e( 'Generate CSV', 'give' ); ?>
							</a>
						</td>
					</tr>

					<tr class="give-export-pdf-sales-earnings">
						<td scope="row" class="row-title">
							<h3>
								<span><?php esc_html_e( 'Export PDF of Donations and Revenue', 'give' ); ?></span>
							</h3>
							<p><?php esc_html_e( 'Download a PDF of Donations and Revenue reports for all forms for the current year.', 'give' ); ?></p>
						</td>
						<td>
							<a class="button" href="<?php echo esc_url( wp_nonce_url( add_query_arg( [ 'give-action' => 'generate_pdf' ] ), 'give_generate_pdf' ) ); ?>">
								<?php esc_html_e( 'Generate PDF', 'give' ); ?>
							</a>
						</td>
					</tr>
					<tr class="give-export-sales-earnings">
						<td scope="row" class="row-title">
							<h3>
								<span><?php esc_html_e( 'Export Revenue and Donation Stats', 'give' ); ?></span>
							</h3>
							<p><?php esc_html_e( 'Download a CSV of revenue and donations over time.', 'give' ); ?></p>
						</td>
						<td>
							<form method="post">
                                <?php
                                // @since 2.21.2
                                // Year in year dropdown should begin from first donation year instead of only display first five recent year.
                                $firstDonation = give()->donations->getFirstDonation();
                                $firstDonationDate = $firstDonation->createdAt ?? null;
                                $currentYear = date('Y', current_time('timestamp'));

                                $start_year_dropdown = Give()->html->year_dropdown(
                                    'start_year',
                                    0,
                                    $firstDonationDate ? ($currentYear - $firstDonationDate->format('Y')) : 0
                                );

                                $end_year_dropdown = Give()->html->year_dropdown(
                                    'end_year',
                                    0,
                                    $firstDonationDate ? ($currentYear - $firstDonationDate->format('Y')) : 0
                                );
                                printf(
                                    esc_html__('%1$s to %2$s', 'give'),
                                    $start_year_dropdown . ' ' . Give()->html->month_dropdown('start_month'),
                                    $end_year_dropdown . ' ' . Give()->html->month_dropdown('end_month')
                                );
                                ?>
								<input type="hidden" name="give-action" value="earnings_export"/>
								<input type="hidden" name="give-nonce" value="<?= wp_create_nonce('give_earnings_export') ?>"/>
								<input type="submit" value="<?php esc_attr_e( 'Generate CSV', 'give' ); ?>" class="button-secondary"/>
							</form>
						</td>
					</tr>

                    <?php
                    /**
                     * @since 2.21.2
                     */
                    do_action( 'give_tools_tab_export_after_donation_history' );
                    ?>

                    <?php
                    /**
                     * @since 2.21.2
                     */
                    do_action( 'give_tools_tab_export_before_core_settings' );
                    ?>

					<tr class="give-export-core-settings">
						<td scope="row" class="row-title">
							<h3>
								<span><?php esc_html_e( 'Export GiveWP Settings', 'give' ); ?></span>
							</h3>
							<p><?php esc_html_e( 'Download an export of Give\'s settings and import it in a new WordPress installation.', 'give' ); ?></p>
						</td>
						<td>
							<form method="post">
								<?php
								$export_excludes = apply_filters( 'give_settings_export_excludes', [] );
								if ( ! empty( $export_excludes ) ) {
									?>
									<i class="settings-excludes-title"><?php esc_html_e( 'Checked options from the list will not be exported.', 'give' ); ?></i>
									<ul class="settings-excludes-list">
										<?php foreach ( $export_excludes as $option_key => $option_label ) { ?>
											<li>
												<label for="settings_export_excludes[<?php echo $option_key; ?>]">
													<input
															type="checkbox"
															checked
															name="settings_export_excludes[<?php echo $option_key; ?>]"
															id="settings_export_excludes[<?php echo $option_key; ?>]"
													/>
													<?php echo esc_html( $option_label ); ?>
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
