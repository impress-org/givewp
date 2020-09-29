<?php

namespace Give\MultiFormGoals\ProgressBar;

use Give\MultiFormGoals\ProgressBar\Model as ProgressBar;

class Block {

	/**
	 * Registers Multi-Form Goals block
	 *
	 * @since 2.9.0
	 **/
	public function addBlock() {
		register_block_type(
			'give/progress-bar',
			[
				'render_callback' => [ $this, 'renderCallback' ],
				'attributes'      => [
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
				],

			]
		);
	}

	/**
	 * Returns Progress Bar block markup
	 *
	 * @since 2.9.0
	 **/
	public function renderCallback( $attributes ) {
		$progressBar = new ProgressBar(
			[
				'ids'        => $attributes['ids'],
				'tags'       => $attributes['tags'],
				'categories' => $attributes['categories'],
				'metric'     => $attributes['metric'],
				'goal'       => $attributes['goal'],
				'color'      => $attributes['color'],
			]
		);
		return $progressBar->getOutput();
	}
}
