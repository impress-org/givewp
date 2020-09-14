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
					'title'       => [
						'type'    => 'string',
						'default' => __( 'We\'ve raised {total} so far!', 'give' ),
					],
					'description' => [
						'type'    => 'string',
						'default' => __( 'But we still need {total_remaining} to reach our goal!', 'give' ),
					],
					'image'       => [
						'type'    => 'string',
						'default' => '',
					],
					'ids'         => [
						'type'    => 'array',
						'default' => [],
					],
					'categories'  => [
						'type'    => 'array',
						'default' => [],
					],
					'tags'        => [
						'type'    => 'array',
						'default' => [],
					],
					'metric'      => [
						'type'    => 'string',
						'default' => 'revenue',
					],
					'deadline'    => [
						'type'    => 'string',
						'default' => '',
					],
					'goal'        => [
						'type'    => 'string',
						'default' => '',
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
				'title'       => $attributes['title'],
				'description' => $attributes['description'],
				'image'       => $attributes['image'],
				'ids'         => $attributes['ids'],
				'tags'        => $attributes['tags'],
				'categories'  => $attributes['categories'],
				'metric'      => $attributes['metric'],
				'deadline'    => $attributes['deadline'],
				'goal'        => $attributes['goal'],
			]
		);
		return $milestone->getOutput();
	}
}
