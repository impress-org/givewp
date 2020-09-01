<?php

namespace Give\Milestones\Block;

class Block {
	public function add_block() {
		register_block_type(
			'give/milestone',
			[
				'render_callback' => [ $this, 'render_callback' ],
				'attributes'      => [
					'title' => [
						'type'    => 'string',
						'default' => __( 'Back to School Fundraiser', 'give' ),
					],
				],

			]
		);
	}
	public function render_callback( $attributes ) {
		ob_start();
		$output = '';
		require $this->get_template_path();
		$output = ob_get_contents();
		ob_end_clean();
		return $output;
	}
	public function get_template_path() {
		return GIVE_PLUGIN_DIR . '/src/Milestones/templates/milestone-block.php';
	}
}
