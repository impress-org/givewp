<?php
/**
 * Admin Payment History
 *
 * @package     Give
 * @subpackage  Admin/Payments
 * @copyright   Copyright (c) 2016, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Payment History Page
 *
 * Renders the payment history page contents.
 *
 * @access      private
 * @since       1.0
 * @return      void
 */
function give_payment_history_page() {
	if ( isset( $_GET['view'] ) && 'view-payment-details' == $_GET['view'] ) {
		require_once GIVE_PLUGIN_DIR . 'includes/admin/payments/view-payment-details.php';
	} else {
		require_once GIVE_PLUGIN_DIR . 'includes/admin/payments/class-payments-table.php';
		$payments_table = new Give_Payment_History_Table();
		$payments_table->prepare_items();
		?>
	<div class="wrap">

		<h1 class="wp-heading-inline"><?php echo get_admin_page_title(); ?></h1>

		<?php
		/**
		 * Fires in payment history screen, at the top of the page.
		 *
		 * @since 1.7
		 */
		do_action( 'give_payments_page_top' );
		?>
		<hr class="wp-header-end">

		<form id="give-payments-advanced-filter" method="get" action="<?php echo admin_url( 'edit.php?post_type=give_forms&page=give-payment-history' ); ?>">
			<input type="hidden" name="post_type" value="give_forms" />
			<input type="hidden" name="page" value="give-payment-history" />
			<?php $payments_table->views(); ?>
			<?php $payments_table->advanced_filters(); ?>
		</form>

		<form id="give-payments-filter" method="get" action="<?php echo admin_url( 'edit.php?post_type=give_forms&page=give-payment-history' ); ?>">
			<input type="hidden" name="post_type" value="give_forms" />
			<input type="hidden" name="page" value="give-payment-history" />
			<?php
			if ( ! empty( $_GET['donor'] ) ) {
				echo sprintf( '<input type="hidden" name="donor" value="%s"/>', absint( $_GET['donor'] ) );
			}

			$payments_table->display();
			?>
		</form>

		<?php
		/**
		 * Fires in payment history screen, at the bottom of the page.
		 *
		 * @since 1.7
		 */
		do_action( 'give_payments_page_bottom' );
		?>

	</div>
		<?php
	}
}

/**
 * Payment History admin titles
 *
 * @since 1.0
 *
 * @param $admin_title
 * @param $title
 * @return string
 */
function give_view_donation_details_title( $admin_title, $title ) {

	if ( 'give_forms_page_give-payment-history' != get_current_screen()->base ) {
		return $admin_title;
	}

	if ( ! isset( $_GET['give-action'] ) ) {
		return $admin_title;
	}

	switch ( $_GET['give-action'] ) :

		case 'view-payment-details':
			$title = sprintf(
				/* translators: %s: admin title */
				esc_html__( 'View Donation Details - %s', 'give' ),
				$admin_title
			);
			break;
		case 'edit-payment':
			$title = sprintf(
				/* translators: %s: admin title */
				esc_html__( 'Edit Donation - %s', 'give' ),
				$admin_title
			);
			break;
		default:
			$title = $admin_title;
			break;
	endswitch;

	return $title;
}
add_filter( 'admin_title', 'give_view_donation_details_title', 10, 2 );

/**
 * Intercept default Edit post links for Give payments and rewrite them to the View Order Details screen.
 *
 * @since 1.0
 *
 * @param $url
 * @param $post_id
 * @param $context
 * @return string
 *
 * @since 2.12.0 default value for the $post_id parameter is removed to prevent PHP8 warnings.
 */
function give_override_edit_post_for_payment_link( $url, $post_id, $context ) {

	$post = get_post( $post_id );

	if ( ! $post ) {
		return $url;
	}

	if ( 'give_payment' != $post->post_type ) {
		return $url;
	}

	$url = admin_url( 'edit.php?post_type=give_forms&page=give-payment-history&view=view-payment-details&id=' . $post_id );

	return $url;
}
add_filter( 'get_edit_post_link', 'give_override_edit_post_for_payment_link', 10, 3 );
