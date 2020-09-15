<?php

namespace Give\Milestones;

use Give\Milestones\Model as Milestone;

class Block {

	/**
	 * Registers Milestone block
	 *
	 * @since 2.9.0
	 **/
	public function addBlock() {
		register_block_type(
			'give/milestone',
			[
				'render_callback' => [ $this, 'renderCallback' ],
				'attributes'      => [
					'message'    => [
						'type'    => 'string',
						'default' => __( 'But we still need {total_remaining} to reach our goal!', 'give' ),
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
						'default' => '',
					],
					'linkText'   => [
						'type'    => 'string',
						'default' => __( 'Learn More', 'give' ),
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
	 * Returns Milestone block markup
	 *
	 * @since 2.9.0
	 **/
	public function renderCallback( $attributes ) {
		$milestone = new Milestone(
			[
				'message'    => $attributes['message'],
				'ids'        => $attributes['ids'],
				'tags'       => $attributes['tags'],
				'categories' => $attributes['categories'],
				'metric'     => $attributes['metric'],
				'goal'       => $attributes['goal'],
				'linkText'   => $attributes['linkText'],
				'linkUrl'    => $attributes['linkUrl'],
				'linkTarget' => $attributes['linkTarget'],
			]
		);
		return $milestone->getOutput();
	}
}
