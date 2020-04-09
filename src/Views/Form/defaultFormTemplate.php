<?php
/**
 * Donation form view.
 *
 * @since 2.7.0
 */
use Give\IframeView;
use function Give\Helpers\Form\Template\Utils\Frontend\getFormId;

$formId = getFormId();
$tm     = new IframeView();

// Fetch the Give Form.
ob_start();
give_get_donation_form( [ 'id' => $formId ] );

$tm->setTitle( get_post_field( 'post_title', $formId ) )
   ->setBody( ob_get_clean() )
   ->render();
