<?php
namespace Give\Helpers\Form\Theme;

use function Give\Helpers\Form\Theme\Utils\Frontend\getFormId;

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
 * Return saved form theme settings
 *
 * @param int    $formId
 * @param string $themeId
 *
 * @return array
 * @since 2.7.0
 */
function get( $formId = null, $themeId = '' ) {
	$formId = $formId ?: getFormId();
	$theme  = $themeId ?: Give()->form_meta->get_meta( $formId, '_give_form_template', true );

	return (array) Give()->form_meta->get_meta( $formId, "_give_{$theme}_form_theme_settings", true );
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
	$theme = Give()->form_meta->get_meta( $formId, '_give_form_template', true );

	return Give()->form_meta->update_meta( $formId, "_give_{$theme}_form_theme_settings", $settings );
}
