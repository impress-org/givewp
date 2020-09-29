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
		$attributes = shortcode_atts(
			[
				'ids'        => [],
				'tags'       => [],
				'categories' => [],
				'metric'     => 'revenue',
				'goal'       => '1000',
				'deadline'   => '',
				'color'      => '#28c77b',
				'heading'    => 'Example Heading',
				'imageSrc'   => 'https://images.pexels.com/photos/142497/pexels-photo-142497.jpeg?auto=compress&cs=tinysrgb&dpr=2&h=750&w=1260',
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
				'metric'     => $attributes['metric'],
				'goal'       => $attributes['goal'],
				'deadline'   => $attributes['deadline'],
				'color'      => $attributes['color'],
				'heading'    => $attributes['heading'],
				'imageSrc'   => $attributes['imageSrc'],
				'summary'    => $attributes['summary'],
			]
		);
		return $multiFormGoal->getOutput();
	}
}
