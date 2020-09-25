<?php

namespace Give\MultiFormGoals;

use Give\MultiFormGoals\Model as MultiFormGoalsModel;

class Block {

	/**
	 * Registers Multi-Form Goals block
	 *
	 * @since 2.9.0
	 **/
	public function addBlock() {
		register_block_type(
			'give/multi-form-goals',
			[
				'render_callback' => [ $this, 'renderCallback' ],
				'attributes'      => [
					'message'    => [
						'type'    => 'string',
						'default' => __( 'So far, we have {total}. We still need {total_remaining} to reach our goal of {total_goal}!', 'give' ),
					],
					'ids'        => [
						'type'    => 'array',
						'default' => [],
					],
					'categories' => [
						'type'    => 'array',
						'default' => [],
					],
					'tags'       => [
						'type'    => 'array',
						'default' => [],
					],
					'metric'     => [
						'type'    => 'string',
						'default' => 'revenue',
					],
					'goal'       => [
						'type'    => 'string',
						'default' => '1000',
					],
					'color'      => [
						'type'    => 'string',
						'default' => '#28c77b',
					],
					'showGoal'   => [
						'type'    => 'boolean',
						'default' => true,
					],
					'linkText'   => [
						'type'    => 'string',
						'default' => __( 'Donate Now', 'give' ),
					],
					'linkUrl'    => [
						'type'    => 'string',
						'default' => '',
					],
					'linkTarget' => [
						'type'    => 'string',
						'default' => '_self',
					],
				],

			]
		);
	}

	/**
	 * Returns Multi-Form Goals block markup
	 *
	 * @since 2.9.0
	 **/
	public function renderCallback( $attributes ) {
		$totals = new MultiFormGoalsModel(
			[
				'message'    => $attributes['message'],
				'ids'        => $attributes['ids'],
				'tags'       => $attributes['tags'],
				'categories' => $attributes['categories'],
				'metric'     => $attributes['metric'],
				'goal'       => $attributes['goal'],
				'color'      => $attributes['color'],
				'showGoal'   => $attributes['showGoal'],
				'linkText'   => $attributes['linkText'],
				'linkUrl'    => $attributes['linkUrl'],
				'linkTarget' => $attributes['linkTarget'],
			]
		);
		return $totals->getOutput();
	}
}
