<?php

namespace Give\MultiFormGoals\MultiFormGoal;

use Give\MultiFormGoals\ProgressBar\Model as ProgressBar;

class Model {

	// Settings for shortcode context
	protected $ids;
	protected $tags;
	protected $categories;
	protected $metric;
	protected $goal;
	protected $color;
	protected $heading;
	protected $summary;
	protected $imageSrc;

	// Settings for block context
	protected $innerBlocks;

	/**
	 * Constructs and sets up setting variables for a new Multi Form Goal model
	 *
	 * @param array $args Arguments for new Multi Form Goal, including 'ids'
	 * @since 2.9.0
	 **/
	public function __construct( array $args ) {
		isset( $args['ids'] ) ? $this->ids                 = $args['ids'] : $this->ids = [];
		isset( $args['tags'] ) ? $this->tags               = $args['tags'] : $this->tags = [];
		isset( $args['categories'] ) ? $this->categories   = $args['categories'] : $this->categories = [];
		isset( $args['metric'] ) ? $this->metric           = $args['metric'] : $this->metric = 'revenue';
		isset( $args['goal'] ) ? $this->goal               = $args['goal'] : $this->goal = '1000';
		isset( $args['color'] ) ? $this->color             = $args['color'] : $this->color = '#28c77b';
		isset( $args['heading'] ) ? $this->heading         = $args['heading'] : $this->heading = 'Example Heading';
		isset( $args['summary'] ) ? $this->summary         = $args['summary'] : $this->color = 'This is a summary.';
		isset( $args['imageSrc'] ) ? $this->imageSrc       = $args['imageSrc'] : $this->color = 'https://images.pexels.com/photos/142497/pexels-photo-142497.jpeg?auto=compress&cs=tinysrgb&dpr=2&h=750&w=1260';
		isset( $args['innerBlocks'] ) ? $this->innerBlocks = $args['innerBlocks'] : $this->innerBlocks = false;
	}

	/**
	 * Get output markup for Multi-Form Goal
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
	 * Get image source for MultiFormGoal
	 *
	 * @return string
	 * @since 2.9.0
	 **/
	public function getImageSrc() {
		return $this->imageSrc;
	}

	/**
	 * Get heading for MultiFormGoal
	 *
	 * @return string
	 * @since 2.9.0
	 **/
	public function getHeading() {
		return $this->heading;
	}

	/**
	 * Get summary for MultiFormGoal
	 *
	 * @return string
	 * @since 2.9.0
	 **/
	public function getSummary() {
		return $this->summary;
	}

	/**
	 * Get Progress Bar output
	 *
	 * @return string
	 * @since 2.9.0
	 **/
	protected function getProgressBarOutput() {
		$progressBar = new ProgressBar(
			[
				'ids'        => $this->ids,
				'tags'       => $this->tags,
				'categories' => $this->categories,
				'metric'     => $this->metric,
				'goal'       => $this->goal,
				'color'      => $this->color,
			]
		);
		return $progressBar->getOutput();
	}

	/**
	 * Get template path for Multi-Form Goal component template
	 * @since 2.9.0
	 **/
	public function getTemplatePath() {
		return GIVE_PLUGIN_DIR . '/src/MultiFormGoals/resources/views/multiformgoal.php';
	}

}
