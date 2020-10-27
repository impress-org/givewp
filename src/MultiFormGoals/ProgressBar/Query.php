<?php

namespace Give\MultiFormGoals\ProgressBar;

/**
 * Get the Total, Count, and Average of the payment totals for published donations of a given set of forms.
 */
class Query {

	/** @var array */
	protected $formIDs;

	/**
	 * @var array $formIDs
	 */
	public function __construct( $formIDs ) {
		global $wpdb;
		$this->wpdb    = $wpdb;
		$this->formIDs = $formIDs;
	}

	/**
	 * @return string
	 */
	public function getSQL() {
		global $wpdb;

		$sql = "
            SELECT
                sum( revenue.amount ) as total,
                count( payment.ID ) as count
            FROM {$wpdb->posts} as payment
                JOIN {$wpdb->give_revenue} as revenue
                    ON revenue.donation_id = payment.ID
            WHERE
                payment.post_type = 'give_payment'
                AND
                payment.post_status IN ( 'publish', 'give_subscription' )
        ";

		if ( ! empty( $this->formIDs ) ) {
			$sql .= '
                AND
                revenue.form_id IN ( ' . $this->getFormsString() . ' )
            ';
		}

		return $sql;
	}

	/**
	 * @return string
	 */
	protected function getFormsString() {
		return implode( ',', $this->formIDs );
	}

	/**
	 * @return stdClass
	 */
	public function getResults() {
		$sql = $this->getSQL();
		return $this->wpdb->get_row( $sql );
	}
}
