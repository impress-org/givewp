<?php

namespace Give\Milestones\Block;

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
		ob_start();
		$output = '';
		require $this->getTemplatePath();
		$output = ob_get_contents();
		ob_end_clean();
		return $output;
	}

	/**
	 * Returns template path used to render Milestone block
	 *
	 * @since 2.9.0
	 **/
	public function getTemplatePath() {
		return GIVE_PLUGIN_DIR . '/src/Milestones/templates/milestone-block.php';
	}
}
