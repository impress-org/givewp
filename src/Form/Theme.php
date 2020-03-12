<?php

/**
 * Handle Theme registration
 *
 * @package Give
 * @since 2.7.0
 */

namespace Give\Form;

use InvalidArgumentException;
use function Give\Helpers\Form\Theme\get as getTheme;

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
	public function __construct( array $args ) {
		$this->data = $args;
		$this->validateArguments();
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
	 * Get form theme path
	 *
	 * @return string
	 */
	public function getThemePath() {
		return $this->data['entry'];
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

		foreach ( $this->data['options'] as $groupID => $option ) {
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

				$field['id'] = "{$this->data['id']}[{$groupID}][{$field['id']}]";

				give_render_field( $field );
			}

			echo '</div></div>';
		}

		return ob_get_clean();
	}


	/**
	 * Validate theme arguments
	 *
	 * @since 2.7.0
	 *
	 * @throws InvalidArgumentException
	 */
	private function validateArguments() {
		$requiredParams = array( 'id', 'name', 'options', 'image', 'entry' );

		if ( array_diff( $requiredParams, array_keys( $this->data ) ) ) {
			throw new InvalidArgumentException(
				sprintf(
					'%1$s<pre>%2$s</pre>',
					__( 'To register a form theme id, name, options and image is required.', 'give' ),
					print_r( $this->data, true )
				)
			);
		}
	}
}
