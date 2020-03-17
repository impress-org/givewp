<?php
namespace Give\Helpers\Form\Theme;

use Give\Form\Theme;
use Give\FormAPI\Form\Field;
use Give\FormAPI\Group;
use WP_Post;
use function Give\Helpers\Form\Theme\get as getTheme;

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
