<?php

/**
 * Handle Theme registration
 *
 * @package Give
 * @since 2.7.0
 */

namespace Give\Form;

use function Give\Form\Theme\getActiveThemeID;
use function Give\Form\Theme\getSavedSettings;

defined( 'ABSPATH' ) || exit;

/**
 * Theme class.
 *
 * @since 2.7.0
 */
class Theme {
	/**
	 * Theme data
	 *
	 * @var array
	 */
	private $data;

	/**
	 * RegisterTheme constructor.
	 *
	 * @param array $args    {
	 *
	 * @type string $id      Theme ID
	 * @type string $name    Theme name
	 * @type string $image   Theme image
	 * @type string $title   Theme title (optional). Can be contain whitelisted HTML tags: strong, a.
	 * @type array  $options Array representation of setting.
	 *
	 * }
	 */
	public function __construct( $args ) {
		$this->data = $args;
	}

	/**
	 * return theme ID.
	 *
	 * @since 2.7.0
	 *
	 * @return string
	 */
	public function getID() {
		return $this->data['id'];
	}

	/**
	 * return theme name.
	 *
	 * @since 2.7.0
	 *
	 * @return string
	 */
	public function geName() {
		return $this->data['name'];
	}

	/**
	 * return theme image.
	 *
	 * @since 2.7.0
	 *
	 * @return string
	 */
	public function getImage() {
		return $this->data['image'];
	}

	/**
	 * return theme options.
	 *
	 * @since 2.7.0
	 *
	 * @return array
	 */
	public function getOptions() {
		return $this->data['options'];
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

		$saveOptions   = getSavedSettings( $post->ID );
		$activeThemeID = getActiveThemeID( $post->ID );

		foreach ( $this->data['options'] as $groupdID => $option ) {
			printf(
				'<div class="give-row %1$s">',
				$groupdID
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
				if (
					$activeThemeID === $this->data['id'] &&
					isset( $saveOptions[ $groupdID ][ $field['id'] ] )
				) {
					$field['attributes']['value'] = $saveOptions[ $groupdID ][ $field['id'] ];
				}

				$field['id'] = "{$this->data['id']}[{$groupdID}][{$field['id']}]";

				give_render_field( $field );
			}

			echo '</div></div>';
		}

		return ob_get_clean();
	}
}
