<?php
namespace Give\Form\Theme;

/**
 * Return active form theme ID
 *
 * @param int $formID
 *
 * @return mixed
 * @since 2.7.0
 */
function getActiveID( $formID ) {
	return Give()->form_meta->get_meta( $formID, '_give_form_theme', true );
}


/**
 * Return saved form theme settings
 *
 * @param int    $formID
 * @param string $themeID
 *
 * @return array
 * @since 2.7.0
 */
function get( $formID, $themeID = '' ) {
	$theme = $themeID ?: Give()->form_meta->get_meta( $formID, '_give_form_theme', true );

	return (array) Give()->form_meta->get_meta( $formID, "_give_{$theme}_form_theme_settings", true );
}

/**
 * Save settings
 *
 * @sinxe 2.7.0
 * @param $formID
 * @param $settings
 *
 * @return mixed
 */
function set( $formID, $settings ) {
	$theme = Give()->form_meta->get_meta( $formID, '_give_form_theme', true );

	return Give()->form_meta->update_meta( $formID, "_give_{$theme}_form_theme_settings", $settings );
}
