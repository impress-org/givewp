<?php
/**
 * Donor Dashboard view
 *
 * @since TBD Escape output.
 * @since 2.10.0
 */

use Give\Views\IframeContentView;

if (!defined('ABSPATH')) {
    exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- template variables; this file is included inside a function, so they are not globals.

$pageId     = give_get_option('donor_dashboard_page');
$iframeView = new IframeContentView();

// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- outputs the complete iframe document built from escaped parts.
echo $iframeView->setTitle(esc_html__('Donor Dashboard', 'give'))->setPostId($pageId)->setBody('<div id="give-donor-dashboard"></div>')->render();
