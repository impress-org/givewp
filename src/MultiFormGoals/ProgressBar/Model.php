<?php

namespace Give\MultiFormGoals\ProgressBar;


class Model {

	// Settings
	protected $ids;
	protected $tags;
	protected $categories;
	protected $metric;
	protected $goal;
	protected $deadline;
	protected $color;

	// Internal
	protected $forms = [];

	/**
	 * Constructs and sets up setting variables for a new Progress Bar model
	 *
	 * @param array $args Arguments for new Progress Bar, including 'ids'
	 * @since 2.9.0
	 **/
	public function __construct( array $args ) {
		isset( $args['ids'] ) ? $this->ids               = $args['ids'] : $this->ids = [];
		isset( $args['tags'] ) ? $this->tags             = $args['tags'] : $this->tags = [];
		isset( $args['categories'] ) ? $this->categories = $args['categories'] : $this->categories = [];
		isset( $args['metric'] ) ? $this->metric         = $args['metric'] : $this->metric = 'revenue';
		isset( $args['goal'] ) ? $this->goal             = $args['goal'] : $this->goal = '1000';
		isset( $args['deadline'] ) ? $this->deadline     = $args['deadline'] : $this->deadline = '';
		isset( $args['color'] ) ? $this->color           = $args['color'] : $this->color = '#28c77b';
	}

	/**
	 * Get forms associated with Progress Bar
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
		$query_args = [
			'post_status' => [
				'publish',
				'give_subscription',
			],
			'number'      => -1,
			'give_forms'  => $this->getForms(),
		];
		$query      = new \Give_Payments_Query( $query_args );
		return $query->get_payments();
	}

	/**
	 * Get output markup for Progress Bar
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
	 * Get raw earnings value for Progress Bar
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
	 * Get number of donors for Progress Bar
	 *
	 * @return int
	 * @since 2.9.0
	 **/
	protected function getDonorCount() {
		$donations = $this->getDonations();
		$donors    = [];
		foreach ( $donations as $donation ) {
			$donors[] = ! empty( $donation->donor_id ) ? $donation->donor_id : 0;
		}
		$unique = array_unique( $donors );
		return count( $unique );
	}

	/**
	 * Get number of donations for Progress Bar
	 *
	 * @return int
	 * @since 2.9.0
	 **/
	protected function getDonationCount() {
		$donations = $this->getDonations();
		return count( $donations );
	}

	/**
	 * Get formatted total remaining (ex: $75)
	 *
	 * @since 2.9.0
	 */
	protected function getFormattedTotalRemaining() {
		switch ( $this->metric ) {
			case 'revenue': {
				$total_remaining = ( $this->getGoal() - $this->getEarnings() ) > 0 ? ( $this->getGoal() - $this->getEarnings() ) : 0;
				return give_currency_filter(
					give_format_amount(
						$total_remaining,
						[
							'sanitize' => false,
							'decimal'  => false,
						]
					)
				);
			}
			case 'donor-count': {
				$total_remaining = ( $this->getGoal() - $this->getDonorCount() ) > 0 ? ( $this->getGoal() - $this->getDonorCount() ) : 0;
				return sprintf( _n( '%d donor', '%d donors', $total_remaining, 'give' ), $total_remaining );
			}
			case 'donation-count': {
				$total_remaining = ( $this->getGoal() - $this->getDonationCount() ) > 0 ? ( $this->getGoal() - $this->getDonationCount() ) : 0;
				return sprintf( _n( '%d donation', '%d donations', $total_remaining, 'give' ), $total_remaining );
			}
		}
	}

	/**
	 * Get goal for Progress Bar
	 *
	 * @return string
	 * @since 2.9.0
	 **/
	protected function getGoal() {
		return $this->goal;
	}

	/**
	 * Get goal color for Progress Bar
	 *
	 * @return string
	 * @since 2.9.0
	 **/
	protected function getColor() {
		return $this->color;
	}
	/**
	 * Get template path for Progress Bar component template
	 * @since 2.9.0
	 **/
	public function getTemplatePath() {
		return GIVE_PLUGIN_DIR . '/src/MultiFormGoals/resources/views/progressbar.php';
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
			case 'donor-count': {
				return sprintf( _n( '%d donor', '%d donors', $total, 'give' ), $total );
			}
			case 'donation-count': {
				return sprintf( _n( '%d donation', '%d donations', $total, 'give' ), $total );
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
			case 'donor-count': {
				return sprintf( _n( '%d donor', '%d donors', $goal, 'give' ), $goal );
			}
			case 'donation-count': {
				return sprintf( _n( '%d donation', '%d donations', $goal, 'give' ), $goal );
			}
		}
	}

	/**
	 * Get deadline for Progress Bar
	 *
	 * @return string
	 * @since 2.9.0
	 **/
	protected function getDeadline() {
		return $this->deadline;
	}

	/**
	 * Get days remaining before Progress Bar deadline
	 *
	 * @return string
	 * @since 2.9.0
	 **/
	protected function getDaysToGo() {
		$now      = new \DateTime();
		$deadline = new \DateTime( $this->getDeadline() );
		return $now < $deadline ? $deadline->diff( $now )->format( '%a' ) + 1 : 0;
	}
}
