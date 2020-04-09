<?php
/**
 * Payment receipt view.
 *
 * @since 2.7.0
 */
use function Give\Helpers\Frontend\getReceiptShortcodeFromConfirmationPage;
use Give\TemplateSkinManager;

$tm = new TemplateSkinManager();

$tm->setTitle( __( 'Donation Receipt', 'give' ) )
   ->setBody( do_shortcode( getReceiptShortcodeFromConfirmationPage() ) )
   ->render();
