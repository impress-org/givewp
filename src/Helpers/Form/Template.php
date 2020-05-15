<?php
namespace Give\Helpers\Form;

use Give\Form\Template\LegacyFormSettingCompatibility;
use Give\Helpers\Form\Template\Utils\Frontend;

class Template {
	/**
	 * This function will return selected form template for a specific form.
	 *
	 * @param int $formId Form id. Default value: check explanation in ./Utils.php:103
	 *
	 * @return string
	 * @since 2.7.0
	 */
	public static function getActiveID( $formId = null ) {
		return Give()->form_meta->get_meta( $formId ?: Frontend::getFormId(), '_give_form_template', true );
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
	public static function getOptions( $formId = null, $templateId = '' ) {
		$formId   = $formId ?: Frontend::getFormId();
		$template = $templateId ?: Give()->form_meta->get_meta( $formId, '_give_form_template', true );
		$settings = Give()->form_meta->get_meta( $formId, "_give_{$template}_form_template_settings", true );

		return $settings ?: [];
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
	public static function saveOptions( $formId, $settings ) {
		$templateId = Give()->form_meta->get_meta( $formId, '_give_form_template', true );

		/* @var \Give\Form\Template $template */
		$template = Give()->templates->getTemplate( $templateId );

		$isUpdated = Give()->form_meta->update_meta( $formId, "_give_{$templateId}_form_template_settings", $settings );

		/*
		 * Below code save legacy setting which connected/mapped to form template setting.
		 * Existing form render on basis of these settings if missed then required output will not generate from give_form_shortcode -> give_get_donation_form function.
		 *
		 * Note: We can remove legacy setting compatibility by returning anything except LegacyFormSettingCompatibility class object.
		 */
		$legacySettingHandler = new LegacyFormSettingCompatibility( $template );
		$legacySettingHandler->save( $formId, $settings );

		return $isUpdated;
	}
}
