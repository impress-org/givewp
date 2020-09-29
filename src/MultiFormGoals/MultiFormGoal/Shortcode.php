<?php

namespace Give\MultiFormGoals\MultiFormGoal;

use Give\MultiFormGoals\MultiFormGoal\Model as MultiFormGoal;

class Shortcode {

	/**
	 * Registers Multi-Form Goal block
	 *
	 * @since 2.9.0
	 **/
	public function addShortcode() {
		add_shortcode( 'give_multi_form_goal', [ $this, 'renderCallback' ] );
	}

	/**
	 * Returns Progress Bar block markup
	 *
	 * @since 2.9.0
	 **/
	public function renderCallback( $attributes ) {
		$attributes    = shortcode_atts(
			[
				'ids'        => [],
				'tags'       => [],
				'categories' => [],
				'metric'     => 'revenue',
				'goal'       => '1000',
				'deadline'   => '',
				'color'      => '#28c77b',
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
			]
		);
		return $multiFormGoal->getOutput();
	}
}
