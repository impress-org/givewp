<?php

namespace Give\Milestones;


class Model {

	// Settings
	protected $title;
	protected $description;
	protected $image;
	protected $ids;
	protected $tags;
	protected $categories;
	protected $metric;
	protected $deadline;
	protected $goal;

	// Internal
	protected $forms = [];

	/**
	 * Constructs and sets up setting variables for a new Milestone model
	 *
	 * @param array $args Arguments for new Milestone, including 'ids' and 'title'
	 * @since 2.9.0
	 **/
	public function __construct( array $args ) {
		isset( $args['title'] ) ? $this->title             = $args['title'] : $this->title = __( 'Sample Milestone Title', 'give' );
		isset( $args['description'] ) ? $this->description = $args['description'] : $this->description = __( 'This is a sample description.', 'give' );
		isset( $args['image'] ) ? $this->image             = $args['image'] : $this->image = '';
		isset( $args['ids'] ) ? $this->ids                 = $args['ids'] : $this->ids = [];
		isset( $args['tags'] ) ? $this->tags               = $args['tags'] : $this->tags = [];
		isset( $args['categories'] ) ? $this->categories   = $args['categories'] : $this->categories = [];
		isset( $args['metric'] ) ? $this->metric           = $args['metric'] : $this->metric = 'revenue';
		isset( $args['deadline'] ) ? $this->deadline       = $args['deadline'] : $this->deadline = '';
		isset( $args['goal'] ) ? $this->goal               = $args['goal'] : $this->goal = '';
	}

	/**
	 * Get forms associated with Milestone
	 *
	 * @return array
	 * @since 2.9.0
	 **/
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

		if ( ! empty( $this->tags ) ) {
			$query_args['tax_query'][] = [
				'taxonomy' => 'give_forms_tag',
				'terms'    => $this->tags,
			];
		}

		if ( ! empty( $this->categories ) ) {
			$query_args['tax_query'][] = [
				'taxonomy' => 'give_forms_category',
				'terms'    => $this->categories,
			];
		}

		$query = new \WP_Query( $query_args );

		if ( $query->posts ) {
			$this->forms = $query->posts;
			return $query->posts;
		} else {
			return false;
		}
	}

	protected function getDonations() {

		$forms    = $this->getForms();
		$form_ids = [];
		foreach ( $forms as $form ) {
			$form_ids += $form['ID'];
		}

		$query_args = [
			'post_status' => [
				'publish',
				'give_subscription',
			],
			'number'      => -1,
			'paged'       => 1,
			'give_forms'  => $form_ids,
		];

		$query = new \Give_Payments_Query( $query_args );
		return $query->get_payments();
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
	 * Get raw earnings value for Milestone
	 *
	 * @return int
	 * @since 2.9.0
	 **/
	protected function getEarnings() {
		$forms    = $this->getForms();
		$earnings = 0;
		foreach ( $forms as $form ) {
			$earnings += ! empty( give_get_meta( $form, '_give_form_earnings', true ) ) ? give_get_meta( $form, '_give_form_earnings', true ) : 0;
		}
		return $earnings;
	}

	/**
	 * Get number of donors for Milestone
	 *
	 * @return int
	 * @since 2.9.0
	 **/
	protected function getDonorCount() {
		$donations = $this->getDonations();
		$donors    = [];
		foreach ( $donations as $donation ) {
			$donors += ! empty( $donation->donor_id ) ? $donation->donor_id : 0;
		}
		$unique = array_unique( $donors );
		return count( $unique );
	}

	/**
	 * Get number of donations for Milestone
	 *
	 * @return int
	 * @since 2.9.0
	 **/
	protected function getDonationCount() {
		$donations = $this->getDonations();
		return count( $donations );
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
	 * Get deadline for Milestone
	 *
	 * @return string
	 * @since 2.9.0
	 **/
	protected function getDeadline() {
		return $this->deadline;
	}

	/**
	 * Get days remaining before Milestone deadline
	 *
	 * @return string
	 * @since 2.9.0
	 **/
	protected function getDaysToGo() {
		$now      = new \DateTime();
		$deadline = new \DateTime( $this->getDeadline() );
		return $now < $deadline ? $deadline->diff( $now )->format( '%a' ) + 1 : 0;
	}

	/**
	 * Get description for Milestone
	 *
	 * @return string
	 * @since 2.9.0
	 **/
	protected function getDescription() {
		return $this->description;
	}

	/**
	 * Get image for Milestone
	 *
	 * @return string
	 * @since 2.9.0
	 **/
	protected function getImage() {
		return $this->image;
	}

	/**
	 * Get goal for Milestone
	 *
	 * @return string
	 * @since 2.9.0
	 **/
	protected function getGoal() {
		return $this->goal;
	}

	/**
	 * Get template path for Milestone component template
	 * @since 2.9.0
	 **/
	public function getTemplatePath() {
		return GIVE_PLUGIN_DIR . '/src/Milestones/resources/views/milestone.php';
	}

	protected function getFormattedTotal() {
		$total = $this->getTotal();
		switch ( $this->metric ) {
			case 'revenue': {
				return give_currency_filter(
					give_format_amount(
						$total,
						[
							'sanitize' => false,
							'decimal'  => false,
						]
					)
				);
			}
			default: {
				return $total;
			}
		}
	}

	protected function getTotal() {
		switch ( $this->metric ) {
			case 'revenue': {
				return $this->getEarnings();
			}
			case 'donor-count': {
				return $this->getDonorCount();
			}
			case 'donation-count': {
				return $this->getDonationCount();
			}
		}
	}

	protected function getFormattedGoal() {
		$goal = $this->getGoal();
		switch ( $this->metric ) {
			case 'revenue': {
				return give_currency_filter(
					give_format_amount(
						$goal,
						[
							'sanitize' => false,
							'decimal'  => false,
						]
					)
				);
			}
			default: {
				return $goal;
			}
		}
	}
}
