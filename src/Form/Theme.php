<?php

/**
 * Handle Theme registration
 *
 * @package Give
 * @since 2.7.0
 */

namespace Give\Form;

use Give\Form\Theme\ThemeOptions;
use InvalidArgumentException;
use function Give\Helpers\Form\Theme\get as getTheme;

defined( 'ABSPATH' ) || exit;

/**
 * Theme class.
 *
 * @since 2.7.0
 */
abstract class Theme {
	/**
	 * return theme ID.
	 *
	 * @since 2.7.0
	 *
	 * @return string
	 */
	abstract  public function getID();

	/**
	 * Get theme name.
	 *
	 * @since 2.7.0
	 *
	 * @return string
	 */
	abstract public function geName();

	/**
	 * Get theme image.
	 *
	 * @since 2.7.0
	 *
	 * @return string
	 */
	abstract public function getImage();

	/**
	 * Gt options config
	 *
	 * @since 2.7.0
	 *
	 * @return string
	 */
	abstract public function getOptionsConfig();

	/**
	 * return theme options.
	 *
	 * @return ThemeOptions
	 * @since 2.7.0
	 */
	public function getOptions() {
		return new ThemeOptions( $this->getOptionsConfig() );
	}

	/**
	 * return theme options.
	 *
	 * @since 2.7.0
	 *
	 * @return string
	 */
	public function renderOptions() {
		global $post;
		ob_start();

		$saveOptions = getTheme( $post->ID, $this->getID() );

		foreach ( $this->getOptions() as $groupID => $option ) {
			printf(
				'<div class="give-row %1$s">',
				$groupID
			);

			printf(
				'<div class="give-row-head">
							<button type="button" class="handlediv" aria-expanded="true">
								<span class="toggle-indicator"/>
							</button>
							<h2 class="hndle"><span>%1$s</span></h2>
						</div>',
				$option['name']
			);

			echo '<div class="give-row-body">';
			foreach ( $option['fields'] as $field ) {
				if ( isset( $saveOptions[ $groupID ][ $field['id'] ] ) ) {
					$field['attributes']['value'] = $saveOptions[ $groupID ][ $field['id'] ];
				}

				$field['id'] = "{$this->getID()}[{$groupID}][{$field['id']}]";

				give_render_field( $field );
			}

			echo '</div></div>';
		}

		return ob_get_clean();
	}
}
