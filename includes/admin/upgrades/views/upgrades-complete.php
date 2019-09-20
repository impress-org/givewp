<?php
/**
 * Upgrades Completed Screen
 *
 * @package     Give
 * @subpackage  Admin/Upgrades
 * @copyright   Copyright (c) 2017, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.8.12
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div id="give-updates" class="wrap give-settings-page">

	<div class="give-settings-header">
		<h1 id="give-updates-h1"
		    class="wp-heading-inline"><?php echo sprintf( __( 'GiveWP %s Updates Complete', 'give' ), '<span class="give-settings-heading-sep dashicons dashicons-arrow-right-alt2"></span>' ); ?></h1>
	</div>

	<div id="give-updates-content">
		<div id="poststuff" class="give-update-panel-content give-clearfix">
			<p>
				<?php echo '🎉 '; ?>
				<?php esc_html_e( 'Congratulations! You are all up to date and running the latest versions of GiveWP and its add-ons.', 'give' ); ?>
			</p>
		</div>
	</div>

</div>
