<?php
namespace Give\Helpers\Form\Template\Utils;

use Give\Form\Template;
use Give\FormAPI\Form\Field;
use Give\FormAPI\Section;
use WP_Post;
use Give\Helpers\Form\Template as FormTemplateUtils;


class Admin {
	/**
	 * Render template setting in form metabox.
	 *
	 * @since 2.7.0
	 *
	 * @global WP_Post $post
	 * @param Template $template
	 * @return string
	 */
	public static function renderMetaboxSettings( $template ) {
		global $post;

		ob_start();

		$saveOptions = FormTemplateUtils::getOptions( $post->ID, $template->getID() );

		/* @var Section $option */
		foreach ( $template->getOptions()->sections as $group ) {
			printf(
				'<div class="give-row %1$s">',
				$group->id
			);

			printf(
				'<div class="give-row-head">
							<button type="button" class="give-handlediv" aria-expanded="true">
								<span class="toggle-indicator"/>
							</button>
							<h2><span>%1$s</span></h2>
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

				$field['id'] = "{$template->getID()}[{$group->id}][{$field['id']}]";

				give_render_field( $field );
			}

			echo '</div></div>';
		}

		return ob_get_clean();
	}
}


