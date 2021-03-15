<?php

namespace Give\MultiFormGoals\MultiFormGoal;

use Give\MultiFormGoals\MultiFormGoal\Model as MultiFormGoal;

class Shortcode {

	/**
	 * @since 2.9.6 Extracted from harded-coded value in `addShortcode()`.
	 * @var string Shortcode tag to be searched in post content.
	 * */
	protected $tag = 'give_multi_form_goal';

	/**
	 * Registers Multi-Form Goal Shortcode
	 *
	 * @since 2.9.0
	 **/
	public function addShortcode() {
		add_shortcode( $this->tag, [ $this, 'renderCallback' ] );
	}

	/**
	 * Returns Shortcode markup
	 *
	 * @since 2.9.0
	 **/
	public function renderCallback( $attributes ) {
		$attributes = $this->parseAttributes(
			[
				'ids'        => [],
				'tags'       => [],
				'categories' => [],
				'goal'       => '1000',
				'enddate'    => '',
				'color'      => '#28c77b',
				'heading'    => 'Example Heading',
				'image'      => GIVE_PLUGIN_URL . 'assets/dist/images/onboarding-preview-form-image.min.jpg',
				'summary'    => 'This is a summary.',

			],
			$attributes,
			'give_multi_form_goal'
		);
		$multiFormGoal = new MultiFormGoal(
			[
				'ids'        => $attributes['ids'],
				'tags'       => $attributes['tags'],
				'categories' => $attributes['categories'],
				'goal'       => $attributes['goal'],
				'enddate'    => $attributes['enddate'],
				'color'      => $attributes['color'],
				'heading'    => $attributes['heading'],
				'imageSrc'   => $attributes['image'],
				'summary'    => $attributes['summary'],
			]
		);
		return $multiFormGoal->getOutput();
	}

	/**
	 * Parse multiple attributes with defualt values and types (infered from the default values).
	 * @link https://developer.wordpress.org/reference/functions/shortcode_atts/
	 * @since 2.9.6
	 * @param array $pairs Entire list of supported attributes and their defaults.
	 * @param array $attributes User defined attributes.
	 * @reutrn array
	 */
	protected function parseAttributes( $pairs, $attributes ) {

		if ( $attributes ) {
			foreach ( $attributes as $key => &$attribute ) {
				if ( isset( $pairs[ $key ] ) && is_array( $pairs[ $key ] ) ) {
					$attribute = $this->parseAttributeArray( $attribute );
				}
			}
		}

		return shortcode_atts( $pairs, $attributes, $this->tag );
	}

	/**
	 * Parses an individual attributes as an array (or from a comma-separated string).
	 * @since 2.9.6
	 * @param string|array $value
	 * @return array
	 */
	protected function parseAttributeArray( $value ) {
		if ( ! is_array( $value ) && ! empty( $value ) ) {
			$value = explode( ',', $value );
		}
		return $value;
	}
}
