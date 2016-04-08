<?php
/**
 * Admin Payment History
 *
 * @package     Give
 * @subpackage  Admin/Payments
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

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
	global $give_options;

	$give_payment = get_post_type_object( 'give_payment' );

	if ( isset( $_GET['view'] ) && 'view-order-details' == $_GET['view'] ) {
		require_once GIVE_PLUGIN_DIR . 'includes/admin/payments/view-order-details.php';
	} else {
		require_once GIVE_PLUGIN_DIR . 'includes/admin/payments/class-payments-table.php';
		$payments_table = new Give_Payment_History_Table();
		$payments_table->prepare_items();
	?>
	<div class="wrap">
		<h2><?php echo $give_payment->labels->menu_name ?></h2>
		<?php do_action( 'give_payments_page_top' ); ?>
		<form id="give-payments-filter" method="get" action="<?php echo admin_url( 'edit.php?post_type=give_forms&page=give-payment-history' ); ?>">
			<input type="hidden" name="post_type" value="give_forms" />
			<input type="hidden" name="page" value="give-payment-history" />

			<?php $payments_table->views() ?>

			<?php $payments_table->advanced_filters(); ?>
			
			<?php $payments_table->display() ?>
		</form>
		<?php do_action( 'give_payments_page_bottom' ); ?>
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
function give_view_order_details_title( $admin_title, $title ) {

	if ( 'give_forms_page_give-payment-history' != get_current_screen()->base )
		return $admin_title;

	if( ! isset( $_GET['give-action'] ) )
		return $admin_title;

	switch( $_GET['give-action'] ) :

		case 'view-order-details' :
			$title = __( 'View Donation Details', 'give' ) . ' - ' . $admin_title;
			break;
		case 'edit-payment' :
			$title = __( 'Edit Payment', 'give' ) . ' - ' . $admin_title;
			break;
		default:
			$title = $admin_title;
			break;
	endswitch;

	return $title;
}
add_filter( 'admin_title', 'give_view_order_details_title', 10, 2 );

/**
 * Intercept default Edit post links for Give payments and rewrite them to the View Order Details screen
 *
 * @since 1.0
 *
 * @param $url
 * @param $post_id
 * @param $context
 * @return string
 */
function give_override_edit_post_for_payment_link( $url, $post_id = 0, $context ) {

	$post = get_post( $post_id );
	if( ! $post )
		return $url;

	if( 'give_payment' != $post->post_type )
		return $url;

	$url = admin_url( 'edit.php?post_type=give_forms&page=give-payment-history&view=view-order-details&id=' . $post_id );

	return $url;
}
add_filter( 'get_edit_post_link', 'give_override_edit_post_for_payment_link', 10, 3 );
