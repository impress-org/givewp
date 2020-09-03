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
						'default' => __( 'Back to School Fundraiser', 'give' ),
					],
					'description' => [
						'type'    => 'string',
						'default' => __( 'This is a sample description.', 'give' ),
					],
					'image'       => [
						'type'    => 'string',
						'default' => '',
					],
					'ids'         => [
						'type'    => 'array',
						'default' => [],
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
				'deadline'    => $attributes['deadline'],
				'goal'        => $attributes['goal'],
			]
		);
		return $milestone->getOutput();
	}
}
