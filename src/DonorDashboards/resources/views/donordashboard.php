<?php
/**
 * Donor Dashboard view
 *
 * @since 2.10.0
 */

use Give\Views\IframeContentView;

$pageId     = give_get_option('donor_dashboard_page');
$iframeView = new IframeContentView();

echo $iframeView
    ->setTitle(esc_html__('Donor Dashboard', 'give'))->setPostId($pageId)
    ->setBody('<div id="give-donor-dashboard"></div>')
    ->render();
