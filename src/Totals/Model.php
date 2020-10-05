<?php

namespace Give\Totals;


class Model {

	// Settings
	protected $message;
	protected $ids;
	protected $tags;
	protected $categories;
	protected $metric;
	protected $goal;
	protected $color;
	protected $showGoal;
	protected $linkText;
	protected $linkUrl;
	protected $linkTarget;

	// Internal
	protected $forms = [];

	/**
	 * Constructs and sets up setting variables for a new Totals model
	 *
	 * @param array $args Arguments for new Totals, including 'ids'
	 * @since 2.9.0
	 **/
	public function __construct( array $args ) {
		isset( $args['message'] ) ? $this->message       = $args['message'] : $this->message = __( 'So far, we have {total}. We still need {total_remaining} to reach our goal of {total_goal}!', 'give' );
		isset( $args['ids'] ) ? $this->ids               = $args['ids'] : $this->ids = [];
		isset( $args['tags'] ) ? $this->tags             = $args['tags'] : $this->tags = [];
		isset( $args['categories'] ) ? $this->categories = $args['categories'] : $this->categories = [];
		isset( $args['metric'] ) ? $this->metric         = $args['metric'] : $this->metric = 'revenue';
		isset( $args['goal'] ) ? $this->goal             = $args['goal'] : $this->goal = '1000';
		isset( $args['color'] ) ? $this->color           = $args['color'] : $this->color = '#28c77b';
		isset( $args['showGoal'] ) ? $this->showGoal     = $args['showGoal'] : $this->showGoal = true;
		isset( $args['linkUrl'] ) ? $this->linkUrl       = $args['linkUrl'] : $this->linkUrl = '';
		isset( $args['linkText'] ) ? $this->linkText     = $args['linkText'] : $this->linkText = __( 'Donate Now', 'give' );
		isset( $args['linkTarget'] ) ? $this->linkTarget = $args['linkTarget'] : $this->linkTarget = '_self';
	}

	/**
	 * Get forms associated with Totals
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
	 * Get output markup for Totals
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
	 * Get raw earnings value for Totals
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
	 * Get number of donors for Totals
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
	 * Get number of donations for Totals
	 *
	 * @return int
	 * @since 2.9.0
	 **/
	protected function getDonationCount() {
		$donations = $this->getDonations();
		return count( $donations );
	}

	/**
	 * Get call to action url for Totals
	 *
	 * @return string
	 * @since 2.9.0
	 **/
	protected function getLinkUrl() {
		return $this->linkUrl;
	}

	/**
	 * Get call to action url for Totals
	 *
	 * @return string
	 * @since 2.9.0
	 **/
	protected function getLinkTarget() {
		return $this->linkTarget;
	}

	/**
	 * Get call to action text for Totals
	 *
	 * @return string
	 * @since 2.9.0
	 **/
	protected function getLinkText() {
		return $this->linkText;
	}

	/**
	 * Get message for Totals
	 *
	 * @return string
	 * @since 2.9.0
	 **/
	protected function getMessage() {
		return $this->formatMessage( $this->message );
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
	 * Format message containing special {} tags (ex: {total})
	 *
	 * @since 2.9.0
	 */
	protected function formatMessage( $message ) {
		$codes = [
			[ 'total', $this->getFormattedTotal() ],
			[ 'total_goal', $this->getFormattedGoal() ],
			[ 'total_remaining', $this->getFormattedTotalRemaining() ],
		];
		foreach ( $codes as $code ) {
			$message = str_replace(
				"{{$code[0]}}",
				$code[1],
				esc_html( $message )
			);
		}
		return $message;
	}

	/**
	 * Get goal for Totals
	 *
	 * @return string
	 * @since 2.9.0
	 **/
	protected function getGoal() {
		return $this->goal;
	}

	/**
	 * Get show goal for Totals
	 *
	 * @return string
	 * @since 2.9.0
	 **/
	protected function getShowGoal() {
		return $this->showGoal;
	}

	/**
	 * Get goal color for Totals
	 *
	 * @return string
	 * @since 2.9.0
	 **/
	protected function getColor() {
		return $this->color;
	}
	/**
	 * Get template path for Totals component template
	 * @since 2.9.0
	 **/
	public function getTemplatePath() {
		return GIVE_PLUGIN_DIR . '/src/Totals/resources/views/totals.php';
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
}
