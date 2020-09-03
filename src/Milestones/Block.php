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
					'title' => [
						'type'    => 'string',
						'default' => __( 'Back to School Fundraiser', 'give' ),
					],
					'ids'   => [
						'type'    => 'array',
						'default' => [],
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
				'ids'   => $attributes['ids'],
				'title' => $attributes['title'],
			]
		);
		return $milestone->getOutput();
	}
}
