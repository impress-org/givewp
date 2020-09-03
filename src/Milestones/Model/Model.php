<?php

namespace Give\Milestones\Model;

class Model {

	// Settings
	protected $ids;
	protected $title;

	// Internal
	protected $forms = [];

	public function __construct( array $args ) {
		isset( $args['ids'] ) ? $this->ids     = $args['ids'] : $this->ids = [];
		isset( $args['title'] ) ? $this->title = $args['title'] : $this->title = __( 'Sample Milestone Title', 'give' );
	}

	protected function getForms() {

		if ( ! empty( $this->forms ) ) {
			return $this->forms;
		}

		$query_args = [
			'post_type'      => 'give_forms',
			'post_status'    => 'publish',
			'post__in'       => $this->ids,
			'posts_per_page' => - 1,
			'fields'         => 'ids',
			'tax_query'      => [
				'relation' => 'AND',
			],
		];
		$query      = new \WP_Query( $query_args );

		if ( $query->posts ) {
			$this->forms = $query->posts;
			return $query->posts;
		} else {
			return false;
		}
	}

	public function getOutput() {
		ob_start();
		$output = '';
		require $this->getTemplatePath();
		$output = ob_get_contents();
		ob_end_clean();
		return $output;
	}

	protected function getEarnings() {
		$forms    = $this->getForms();
		$earnings = 0;
		foreach ( $forms as $form ) {
			$earnings += ! empty( give_get_meta( $form, '_give_form_earnings', true ) ) ? give_get_meta( $form, '_give_form_earnings', true ) : 0;
		}
		return $earnings;
	}

	protected function getTitle() {
		return $this->title;
	}

	public function getTemplatePath() {
		return GIVE_PLUGIN_DIR . '/src/Milestones/templates/milestone.php';
	}
}
