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
		$sql = "
            SELECT
                sum( meta_value ) as total,
                count( meta_value ) as count,
                avg( meta_value ) as average
            FROM wp_posts as payment
                JOIN wp_give_donationmeta as meta
                    ON meta.donation_id = payment.ID
            WHERE
                payment.post_type = 'give_payment'
                AND
                meta.meta_key = '_give_payment_total'
                AND
                payment.post_status = 'publish'
        ";

		if ( ! empty( $this->formIDs ) ) {
			$sql .= "
                AND
                payment.ID IN (
                    SELECT donation_id  
                    FROM wp_give_donationmeta
                    WHERE meta_key = '_give_payment_form_id'
                    AND meta_value IN ( " . $this->getFormsString() . ' )
                )
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
		return $this->wpdb->get_row(
			$this->getSQL()
		);
	}
}
