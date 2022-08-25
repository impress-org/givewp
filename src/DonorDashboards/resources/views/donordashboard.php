<?php
/**
 * Donor Dashboard view
 *
 * @since 2.10.0
 */

use Give\Helpers\Form\Template\Utils\Frontend as FrontendFormTemplateUtils;
use Give\Views\IframeContentView;

$formId     = FrontendFormTemplateUtils::getFormId();
$iframeView = new IframeContentView();

echo $iframeView
    ->setTitle(esc_html__('Donor Dashboard', 'give'))->setFormId($formId)
    ->setBody('<div id="give-donor-dashboard"></div>')
    ->render();
