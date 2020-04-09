<?php
/**
 * Payment confirmation view.
 *
 * @since 2.7.0
 */
use Give\IframeView;

$tm = new IframeView();

$tm->setTitle( __( 'Donation Processing', 'give' ) )
	->setBody( apply_filters( 'give_payment_confirm_' . give_clean( $_GET['payment-confirmation'] ), '' ) )
	->render();
