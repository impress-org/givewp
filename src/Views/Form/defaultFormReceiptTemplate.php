<?php
/**
 * Payment receipt view.
 *
 * @since 2.7.0
 */
use function Give\Helpers\Frontend\getReceiptShortcodeFromConfirmationPage;
use Give\Views\IframeContentView;

$iframeView = new IframeContentView();

echo $iframeView->setTitle( __( 'Donation Receipt', 'give' ) )
   ->setBody( do_shortcode( getReceiptShortcodeFromConfirmationPage() ) )
   ->render();
