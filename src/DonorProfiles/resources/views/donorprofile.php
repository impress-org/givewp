<?php
/**
 * Payment receipt view.
 *
 * @since 2.7.0
 */
use Give\Views\IframeContentView;

$iframeView = new IframeContentView();

echo $iframeView
	->setTitle( esc_html__( 'Donor Profile', 'give' ) )
	->setBody( '<div id="give-donor-profile"></div>' )
	->render();
