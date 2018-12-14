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
<div class="wrap" id="poststuff">
	<div id="give-updates">
		<h1 id="give-updates-h1"><?php esc_html_e( 'Give - Updates Complete', 'give' ); ?></h1>
		<hr class="wp-header-end">

		<div class="give-update-panel-content">
			<p><?php esc_html_e( 'Congratulations! You are running the latest versions of Give and its add-ons.', 'give' ); ?></p>
		</div>

	</div>
</div>
