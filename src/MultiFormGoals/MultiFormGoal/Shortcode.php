<?php

namespace Give\MultiFormGoals\MultiFormGoal;

use Give\MultiFormGoals\MultiFormGoal\Model as MultiFormGoal;

class Shortcode {

	/**
	 * Registers Multi-Form Goal Shortcode
	 *
	 * @since 2.9.0
	 **/
	public function addShortcode() {
		add_shortcode( 'give_multi_form_goal', [ $this, 'renderCallback' ] );
	}

	/**
	 * Returns Shortcode markup
	 *
	 * @since 2.9.0
	 **/
	public function renderCallback( $attributes ) {
		error_log( serialize( $attributes ) );
		$attributes = shortcode_atts(
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
}
