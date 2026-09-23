<?php
/**
 * Payment receipt view.
 *
 * @since TBD Escape output.
 * @since 2.7.0
 */

use Give\Views\IframeContentView;

$pageId     = give_get_option('success_page');
$iframeView = new IframeContentView();

// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- IframeContentView::render() escapes internally.
echo $iframeView->setTitle(esc_html__('Donation Receipt', 'give'))->setPostId($pageId)->setBody('<div id="give-receipt"></div>')->render();
