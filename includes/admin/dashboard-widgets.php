<?php
/**
 * Dashboard Widgets
 *
 * @package     Give
 * @subpackage  Admin/Dashboard
 * @copyright   Copyright (c) 2016, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

use Give\DonationForms\DonationFormsAdminPage;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add donation forms count to dashboard "At a glance" widget
 *
 * @since  1.0
 *
 * @param $items
 *
 * @return array
 */
function give_dashboard_at_a_glance_widget( $items ) {

	$num_posts = wp_count_posts( 'give_forms' );

	if ( $num_posts && $num_posts->publish ) {

		$text = sprintf(
			/* translators: %s: number of posts published */
			_n( '%s GiveWP Form', '%s GiveWP Forms', $num_posts->publish, 'give' ),
			$num_posts->publish
		);

		$text = sprintf( $text, number_format_i18n( $num_posts->publish ) );

		if ( current_user_can( 'edit_give_forms', get_current_user_id() ) ) {
			$text = sprintf(
				'<a class="give-forms-count" href="%1$s">%2$s</a>',
				DonationFormsAdminPage::getUrl(),
				$text
			);
		} else {
			$text = sprintf(
				'<span class="give-forms-count">%1$s</span>',
				$text
			);
		}

		$items[] = $text;
	}

	return $items;
}

add_filter( 'dashboard_glance_items', 'give_dashboard_at_a_glance_widget', 1, 1 );
