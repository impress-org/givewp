<?php
/**
 * Upgrade Screen
 *
 * @package     Give
 * @subpackage  Admin/Upgrades
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render Upgrades Screen
 *
 * @since 1.0
 * @return void
 */
function give_upgrades_screen() {
	$action = isset( $_GET['give-upgrade'] ) ? sanitize_text_field( $_GET['give-upgrade'] ) : '';
	$step   = isset( $_GET['step'] ) ? absint( $_GET['step'] ) : 1;
	$total  = isset( $_GET['total'] ) ? absint( $_GET['total'] ) : false;
	$custom = isset( $_GET['custom'] ) ? absint( $_GET['custom'] ) : 0;
	$number = isset( $_GET['number'] ) ? absint( $_GET['number'] ) : 100;
	$steps  = round( ( $total / $number ), 0 );

	$doing_upgrade_args = array(
		'page'         => 'give-upgrades',
		'give-upgrade' => $action,
		'step'         => $step,
		'total'        => $total,
		'custom'       => $custom,
		'steps'        => $steps
	);
	update_option( 'give_doing_upgrade', $doing_upgrade_args );
	if ( $step > $steps ) {
		// Prevent a weird case where the estimate was off. Usually only a couple.
		$steps = $step;
	}
	?>
	<div class="wrap">
		<h2><?php _e( 'Give - Upgrades', 'give' ); ?></h2>

		<?php if ( ! empty( $action ) ) : ?>

			<div id="give-upgrade-status">
				<p style="font-size: 20px;max-width: 900px;"><?php _e( 'The upgrade process has started, please be patient and do not close this window or navigate away from this page. This could take several minutes depending on the upgrade and the size of your website. You will be automatically redirected when the upgrade is finished.', 'give' ); ?>
					<img src="<?php echo GIVE_PLUGIN_URL . '/assets/images/spinner.gif'; ?>" id="give-upgrade-loader" style="  position: relative; top: 3px; left: 6px;" />
				</p>

				<?php if ( ! empty( $total ) ) : ?>
					<p>
						<strong><?php printf( __( 'Step %d of approximately %d running', 'give' ), $step, $steps ); ?></strong>
					</p>
				<?php endif; ?>
			</div>
			<script type="text/javascript">
				setTimeout( function () {
					document.location.href = "index.php?give_action=<?php echo $action; ?>&step=<?php echo $step; ?>&total=<?php echo $total; ?>&custom=<?php echo $custom; ?>";
				}, 250 );
			</script>

		<?php else : ?>

			<div id="give-upgrade-status">
				<p style="font-size: 20px;max-width: 900px;">
					<?php _e( 'The upgrade process has started, please be patient and do not close this window or navigate away from this page. This could take several minutes depending on the upgrade and the size of your website. You will be automatically redirected when the upgrade is finished.', 'give' ); ?>
					<img src="<?php echo GIVE_PLUGIN_URL . '/assets/images/spinner.gif'; ?>" id="give-upgrade-loader" style="  position: relative; top: 3px; left: 6px;" />
				</p>
			</div>
			<script type="text/javascript">
				jQuery( document ).ready( function () {
					// Trigger upgrades on page load
					var data = {action: 'give_trigger_upgrades'};
					jQuery.post( ajaxurl, data, function ( response ) {
						if ( response == 'complete' ) {
							jQuery( '#give-upgrade-loader' ).hide();
							document.location.href = 'index.php?page=give-about'; // Redirect to the welcome page
						}
					} );
				} );
			</script>

		<?php endif; ?>

	</div>
<?php
}
