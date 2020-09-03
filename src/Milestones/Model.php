<?php

namespace Give\Milestones;


class Model {

	// Settings
	protected $title;

	/**
	 * Constructs and sets up setting variables for a new Milestone model
	 *
	 * @param array $args Arguments for new Milestone, including 'ids' and 'title'
	 * @since 2.9.0
	 **/
	public function __construct( array $args ) {
		isset( $args['title'] ) ? $this->title = $args['title'] : $this->title = __( 'Sample Milestone Title', 'give' );
	}

	/**
	 * Get output markup for Milestone
	 *
	 * @return string
	 * @since 2.9.0
	 **/
	public function getOutput() {
		ob_start();
		$output = '';
		require $this->getTemplatePath();
		$output = ob_get_contents();
		ob_end_clean();
		return $output;
	}

	/**
	 * Get title for Milestone
	 *
	 * @return string
	 * @since 2.9.0
	 **/
	protected function getTitle() {
		return $this->title;
	}

	/**
	 * Get template path for Milestone component template
	 *
	 * @return string
	 * @since 2.9.0
	 **/
	public function getTemplatePath() {
		return GIVE_PLUGIN_DIR . '/src/Milestones/resources/views/milestone.php';
	}
}
