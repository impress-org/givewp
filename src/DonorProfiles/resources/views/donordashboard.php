<?php
/**
 * Donor Dashboard view
 *
 * @since 2.10.0
 */
use Give\Views\IframeContentView;

$iframeView = new IframeContentView();

echo $iframeView
	->setTitle( esc_html__( 'Donor Dashboard', 'give' ) )
	->setBody( '<div id="give-donor-dashboard"></div>' )
	->render();
