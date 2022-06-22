<?php
/**
 * Manual Gateway
 *
 * @package     Give
 * @subpackage  Gateways
 * @copyright   Copyright (c) 2016, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Give\Helpers\Form\Utils as FormUtils;

/**
 * Manual Gateway does not need a CC form, so remove it.
 *
 * @since 1.0
 * @return void
 */

/**
 * Manual Gateway form output
 *
 * Manual Gateway does not use a CC form, but it does display a note about test donations
 *
 * @return bool
 **/
function give_manual_form_output( $form_id ) {
	if ( FormUtils::isLegacyForm( $form_id ) ) {
		return false;
	}

	printf(
		'
		<fieldset class="no-fields">
			<div style="display: flex; justify-content: center; margin-top: 20px;">
				<svg width="84" height="67" viewBox="0 0 84 67" fill="none" xmlns="http://www.w3.org/2000/svg">
					<g clip-path="url(#clip0)">
						<path d="M67.0133 24.9951L65.9403 26.8663C65.5477 27.5599 64.7102 27.8478 63.9643 27.573C62.4202 26.9972 61.0069 26.1728 59.7637 25.139C59.1618 24.6417 59.0047 23.765 59.3973 23.0845L60.4704 21.2132C59.5674 20.1663 58.8608 18.9494 58.3897 17.6277H56.2305C55.4454 17.6277 54.7649 17.065 54.634 16.2798C54.3723 14.7095 54.3592 13.0607 54.634 11.4249C54.7649 10.6398 55.4454 10.064 56.2305 10.064H58.3897C58.8608 8.74232 59.5674 7.52533 60.4704 6.47846L59.3973 4.60717C59.0047 3.9267 59.1487 3.04994 59.7637 2.55268C61.0069 1.51889 62.4333 0.694473 63.9643 0.118692C64.7102 -0.156113 65.5477 0.131778 65.9403 0.825333L67.0133 2.69662C68.3874 2.44799 69.7876 2.44799 71.1616 2.69662L72.2346 0.825333C72.6272 0.131778 73.4647 -0.156113 74.2106 0.118692C75.7547 0.694473 77.168 1.51889 78.4112 2.55268C79.0131 3.04994 79.1702 3.9267 78.7776 4.60717L77.7046 6.47846C78.6075 7.52533 79.3141 8.74232 79.7852 10.064H81.9444C82.7296 10.064 83.41 10.6267 83.5409 11.4119C83.8026 12.9822 83.8157 14.631 83.5409 16.2667C83.41 17.0519 82.7296 17.6277 81.9444 17.6277H79.7852C79.3141 18.9494 78.6075 20.1663 77.7046 21.2132L78.7776 23.0845C79.1702 23.765 79.0262 24.6417 78.4112 25.139C77.168 26.1728 75.7417 26.9972 74.2106 27.573C73.4647 27.8478 72.6272 27.5599 72.2346 26.8663L71.1616 24.9951C69.8006 25.2437 68.3874 25.2437 67.0133 24.9951ZM65.6393 17.3005C70.6774 21.174 76.4221 15.4292 72.5487 10.3912C67.5106 6.50463 61.7659 12.2624 65.6393 17.3005ZM50.5512 37.4398L54.9612 39.6382C56.2829 40.3972 56.8587 42.0068 56.3352 43.4462C55.1706 46.613 52.8805 49.5181 50.7606 52.0568C49.7922 53.2214 48.1172 53.5093 46.7956 52.7503L42.9876 50.5519C40.8938 52.3447 38.4598 53.771 35.8034 54.7001V59.097C35.8034 60.615 34.7172 61.9236 33.2254 62.1853C30.0063 62.7349 26.6301 62.7611 23.2932 62.1853C21.7883 61.9236 20.676 60.6281 20.676 59.097V54.7001C18.0196 53.7579 15.5856 52.3447 13.4919 50.5519L9.68385 52.7372C8.37525 53.4962 6.68717 53.2083 5.71881 52.0437C3.59889 49.505 1.36119 46.5999 0.196541 43.4462C-0.326896 42.0199 0.248885 40.4103 1.57056 39.6382L5.92818 37.4398C5.41783 34.7048 5.41783 31.8913 5.92818 29.1433L1.57056 26.9318C0.248885 26.1728 -0.339982 24.5632 0.196541 23.1369C1.36119 19.9701 3.59889 17.065 5.71881 14.5263C6.68717 13.3617 8.36217 13.0738 9.68385 13.8328L13.4919 16.0312C15.5856 14.2384 18.0196 12.8121 20.676 11.883V7.47299C20.676 5.96811 21.7491 4.65951 23.2409 4.39779C26.46 3.84818 29.8493 3.82201 33.1862 4.38471C34.6911 4.64643 35.8034 5.94193 35.8034 7.47299V11.8699C38.4598 12.8121 40.8938 14.2253 42.9876 16.0181L46.7956 13.8197C48.1042 13.0607 49.7922 13.3486 50.7606 14.5132C52.8805 17.0519 55.1051 19.957 56.2698 23.1238C56.7932 24.5501 56.2829 26.1597 54.9612 26.9318L50.5512 29.1302C51.0616 31.8783 51.0616 34.6917 50.5512 37.4398ZM35.1622 40.2009C42.909 30.1247 31.4065 18.6222 21.3303 26.3691C13.5835 36.4453 25.086 47.9478 35.1622 40.2009ZM67.0133 64.1089L65.9403 65.9802C65.5477 66.6738 64.7102 66.9617 63.9643 66.6869C62.4202 66.1111 61.0069 65.2867 59.7637 64.2529C59.1618 63.7556 59.0047 62.8788 59.3973 62.1984L60.4704 60.3271C59.5674 59.2802 58.8608 58.0632 58.3897 56.7415H56.2305C55.4454 56.7415 54.7649 56.1788 54.634 55.3937C54.3723 53.8234 54.3592 52.1746 54.634 50.5388C54.7649 49.7537 55.4454 49.1779 56.2305 49.1779H58.3897C58.8608 47.8562 59.5674 46.6392 60.4704 45.5923L59.3973 43.721C59.0047 43.0406 59.1487 42.1638 59.7637 41.6665C61.0069 40.6328 62.4333 39.8083 63.9643 39.2326C64.7102 38.9578 65.5477 39.2456 65.9403 39.9392L67.0133 41.8105C68.3874 41.5619 69.7876 41.5619 71.1616 41.8105L72.2346 39.9392C72.6272 39.2456 73.4647 38.9578 74.2106 39.2326C75.7547 39.8083 77.168 40.6328 78.4112 41.6665C79.0131 42.1638 79.1702 43.0406 78.7776 43.721L77.7046 45.5923C78.6075 46.6392 79.3141 47.8562 79.7852 49.1779H81.9444C82.7296 49.1779 83.41 49.7406 83.5409 50.5257C83.8026 52.096 83.8157 53.7449 83.5409 55.3806C83.41 56.1658 82.7296 56.7415 81.9444 56.7415H79.7852C79.3141 58.0632 78.6075 59.2802 77.7046 60.3271L78.7776 62.1984C79.1702 62.8788 79.0262 63.7556 78.4112 64.2529C77.168 65.2867 75.7417 66.1111 74.2106 66.6869C73.4647 66.9617 72.6272 66.6738 72.2346 65.9802L71.1616 64.1089C69.8006 64.3576 68.3874 64.3576 67.0133 64.1089ZM65.6393 56.4013C70.6774 60.2747 76.4221 54.53 72.5487 49.4919C67.5106 45.6185 61.7659 51.3632 65.6393 56.4013Z" fill="#989898"/>
					</g>
					<defs>
						<clipPath id="clip0">
							<rect width="83.75" height="67" fill="white"/>
						</clipPath>
					</defs>
				</svg>
			</div>
			<p style="text-align: center;"><b>%1$s</b></p>
			<p style="text-align: center;">
				<b>%2$s</b> %3$s
			</p>
		</fieldset>
		',
		esc_html__( 'Test GiveWP with the Test Donation Gateway', 'give' ),
		esc_html__( 'How it works:', 'give' ),
		esc_html__( 'There are no fields for this gateway and you will not be charged. This payment option is only for you to test the donation experience.', 'give' )
	);
	return true;
}
add_action( 'give_manual_cc_form', 'give_manual_form_output' );

/**
 * Processes the donation data and uses the Manual Payment gateway to record
 * the donation in the Donation History
 *
 * @since 1.0
 *
 * @param array $purchase_data Donation Data
 *
 * @return void
 */
function give_manual_payment( $purchase_data ) {

	if ( ! wp_verify_nonce( $purchase_data['gateway_nonce'], 'give-gateway' ) ) {
		wp_die( esc_html__( 'We\'re unable to recognize your session. Please refresh the screen to try again; otherwise contact your website administrator for assistance.', 'give' ), esc_html__( 'Error', 'give' ), [ 'response' => 403 ] );
	}

	// Create payment_data array
	$payment_data = [
		'price'           => $purchase_data['price'],
		'give_form_title' => $purchase_data['post_data']['give-form-title'],
		'give_form_id'    => intval( $purchase_data['post_data']['give-form-id'] ),
		'give_price_id'   => isset( $purchase_data['post_data']['give-price-id'] ) ? $purchase_data['post_data']['give-price-id'] : '',
		'date'            => $purchase_data['date'],
		'user_email'      => $purchase_data['user_email'],
		'purchase_key'    => $purchase_data['purchase_key'],
		'currency'        => give_get_currency( $purchase_data['post_data']['give-form-id'], $purchase_data ),
		'user_info'       => $purchase_data['user_info'],
		'status'          => 'pending',
	];
	// Record the pending payment
	$payment = give_insert_payment( $payment_data );

	if ( $payment ) {
        $is_anonymous = isset($purchase_data['post_data']['give_anonymous_donation']) && absint($purchase_data['post_data']['give_anonymous_donation']);
        update_post_meta($payment, '_give_anonymous_donation', $is_anonymous);
		give_update_payment_status( $payment, 'publish' );
		give_send_to_success_page();
	} else {
		give_record_gateway_error(
			esc_html__( 'Payment Error', 'give' ),
			sprintf(
				/* translators: %s: payment data */
				esc_html__( 'The payment creation failed while processing a manual (free or test) donation. Payment data: %s', 'give' ),
				json_encode( $payment_data )
			),
			$payment
		);
		// If errors are present, send the user back to the donation page so they can be corrected
		give_send_back_to_checkout( '?payment-mode=' . $purchase_data['post_data']['give-gateway'] );
	}
}

add_action( 'give_gateway_manual', 'give_manual_payment' );
