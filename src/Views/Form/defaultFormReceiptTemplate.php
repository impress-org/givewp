<?php
/**
 * Payment receipt view.
 *
 * @since 2.7.0
 */
use function Give\Helpers\Frontend\getReceiptShortcodeFromConfirmationPage;
use Give\IframeView;

$tm = new IframeView();

$tm->setTitle( __( 'Donation Receipt', 'give' ) )
   ->setBody( do_shortcode( getReceiptShortcodeFromConfirmationPage() ) )
   ->render();
