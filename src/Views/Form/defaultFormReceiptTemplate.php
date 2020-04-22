<?php

use Give\Views\IframeView;

$iframeView = new IframeView();

echo $iframeView->setTitle( __( 'Donation Receipt', 'give' ) )
	->setBody( '<div id="give-receipt"></div>' )->render();
