<?php
namespace Give\Helpers\Form\Theme;

use Give\Form\Theme;
use Give\FormAPI\Form\Field;
use Give\FormAPI\Group;
use WP_Post;
use function Give\Helpers\Form\Theme\get as getTheme;
use function Give\Helpers\Form\Utils\getFormId;

/**
 * This function will return selected form template for a specific form.
 *
 * @param int $formId Form id. Default value: check explanation in ./Utils.php:103
 *
 * @return string
 * @since 2.7.0
 */
function getActiveID( $formId = null ) {
	return Give()->form_meta->get_meta( $formId ?: getFormId(), '_give_form_theme', true );
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
	$theme  = $themeId ?: Give()->form_meta->get_meta( $formId, '_give_form_theme', true );

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
	$theme = Give()->form_meta->get_meta( $formId, '_give_form_theme', true );

	return Give()->form_meta->update_meta( $formId, "_give_{$theme}_form_theme_settings", $settings );
}

/**
 * Render theme setting in form metabox.
 *
 * @since 2.7.0
 *
 * @global WP_Post $post
 * @param Theme $theme
 * @return string
 */
function renderMetaboxSettings( $theme ) {
	global $post;

	ob_start();

	$saveOptions = getTheme( $post->ID, $theme->getID() );

	/* @var Group $option */
	foreach ( $theme->getOptions()->groups as $group ) {
		printf(
			'<div class="give-row %1$s">',
			$group->id
		);

		printf(
			'<div class="give-row-head">
							<button type="button" class="handlediv" aria-expanded="true">
								<span class="toggle-indicator"/>
							</button>
							<h2 class="hndle"><span>%1$s</span></h2>
						</div>',
			$group->name
		);

		echo '<div class="give-row-body">';

		/* @var Field $field */
		foreach ( $group->fields as $field ) {
			$field = $field->toArray();
			if ( isset( $saveOptions[ $group->id ][ $field['id'] ] ) ) {
				$field['attributes']['value'] = $saveOptions[ $group->id ][ $field['id'] ];
			}

			$field['id'] = "{$theme->getID()}[{$group->id}][{$field['id']}]";

			give_render_field( $field );
		}

		echo '</div></div>';
	}

	return ob_get_clean();
}
