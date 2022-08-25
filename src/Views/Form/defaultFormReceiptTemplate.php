<?php
/**
 * Payment receipt view.
 *
 * @since 2.7.0
 */

use Give\Helpers\Form\Template\Utils\Frontend as FrontendFormTemplateUtils;
use Give\Views\IframeContentView;

$formId     = FrontendFormTemplateUtils::getFormId();
$iframeView = new IframeContentView();

echo $iframeView->setTitle(esc_html__('Donation Receipt', 'give'))->setFormId($formId)
                ->setBody('<div id="give-receipt"></div>')->render();
