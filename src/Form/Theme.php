<?php

/**
 * Handle Theme registration
 *
 * @package Give
 * @since 2.7.0
 */

namespace Give\Form;

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
	 * @return string
	 */
	public function renderOptions() {
		ob_start();

		foreach ( $this->data['options'] as $groupdID => $option ) {
			printf(
				'<div class="give-row %1$s">',
				$groupdID
			);

			printf(
				'<div class="give-row-head">
							<button type="button" class="handlediv" aria-expanded="true">
								<span class="toggle-indicator"></span>
							</button>
							<h2 class="hndle"><span>%1$s</span></h2>
						</div>',
				$option['name']
			);

			echo '<div class="give-row-body">';
			foreach ( $option['fields'] as $field ) {
				$field['id'] = "{$this->data['id']}[{$field['id']}]";

				give_render_field( $field );
			}

			echo '</div></div>';
		}

		return ob_get_clean();
	}
}
