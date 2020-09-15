<?php

namespace Give\Totals;

use Give\Totals\Model as TotalsModel;

class Block {

	/**
	 * Registers Totals block
	 *
	 * @since 2.9.0
	 **/
	public function addBlock() {
		register_block_type(
			'give/totals',
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
						'default' => '100',
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
	 * Returns Totals block markup
	 *
	 * @since 2.9.0
	 **/
	public function renderCallback( $attributes ) {
		$totals = new TotalsModel(
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
		return $totals->getOutput();
	}
}
