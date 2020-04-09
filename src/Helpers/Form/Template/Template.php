<?php
namespace Give\Helpers\Form\Template;

use Give\Form\Template;
use Give\Form\Template\LegacyFormSettingCompatibility;
use function Give\Helpers\Form\Template\Utils\Frontend\getFormId;

/**
 * This function will return selected form template for a specific form.
 *
 * @param int $formId Form id. Default value: check explanation in ./Utils.php:103
 *
 * @return string
 * @since 2.7.0
 */
function getActiveID( $formId = null ) {
	return Give()->form_meta->get_meta( $formId ?: getFormId(), '_give_form_template', true );
}


/**
 * Return saved form template settings
 *
 * @param int    $formId
 * @param string $templateId
 *
 * @return array
 * @since 2.7.0
 */
function get( $formId = null, $templateId = '' ) {
	$formId   = $formId ?: getFormId();
	$template = $templateId ?: Give()->form_meta->get_meta( $formId, '_give_form_template', true );

	return (array) Give()->form_meta->get_meta( $formId, "_give_{$template}_form_template_settings", true );
}

/**
 * Save settings
 *
 * @sinxe 2.7.0
 * @param $formId
 * @param $settings
 *
 * @return mixed
 */
function set( $formId, $settings ) {
	$template = Give()->form_meta->get_meta( $formId, '_give_form_template', true );

	$isUpdated = Give()->form_meta->update_meta( $formId, "_give_{$template}_form_template_settings", $settings );

	/*
	 * Below code save legacy setting which connected/mapped to form template setting.
	 * Existing form render on basis of these settings if missed then required output will not generate from give_form_shortcode -> give_get_donation_form function.
	 *
	 * Note: We can remove legacy setting compatibility by returning anything except LegacyFormSettingCompatibility class object.
	 */
	/* @var LegacyFormSettingCompatibility $legacySettingHandler */
	$legacySettingHandler = Give()->templates->getTemplate( $template )->getLegacySettingHandler();
	if ( $legacySettingHandler instanceof LegacyFormSettingCompatibility ) {
		$legacySettingHandler->save( $formId, $settings );
	}

	return $isUpdated;
}
