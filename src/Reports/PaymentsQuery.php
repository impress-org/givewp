<?php

namespace Give\Reports;

/**
 * Builds the optimized SQL of Donations joined with various donation meta.
 */
class PaymentsQuery {

	/** @var string test|live */
	protected $testMode = 'live';

	/** @var string */
	protected $currency = 'USD';

	/** @var string */
	protected $startDate;

	/** @var string */
	protected $endDate;

	/** @var int */
	protected $number = -1;

	/**
	 * @param bool $testMode
	 *
	 * @return PaymentsQuery
	 */
	public function testMode( $testMode = true ) {
		$this->testMode = ( $testMode ) ? 'test' : 'live';
		return $this;
	}

	/**
	 * @param string $currency
	 *
	 * @return PaymentsQuery
	 */
	public function currency( $currency ) {
		$this->currency = $currency;
		return $this;
	}

	/**
	 * @param string $startDate
	 * @param string $endDate
	 *
	 * @return PaymentsQuery
	 */
	public function between( $startDate, $endDate ) {
		$this->startDate = $startDate;
		$this->endDate   = $endDate;
		return $this;
	}

	/**
	 * @param int $number
	 *
	 * @return PaymentsQuery
	 */
	public function limit( $number ) {
		$this->number = $number;
		return $this;
	}

	/**
	 * @param \wpdb $wpdb
	 * @return string
	 */
	public function getSQL( $wpdb ) {

		/**
		 * The donation meta table is joined for each needed meta key/value.
		 * Each key has a coresponding SELECT item to alias the value.
		 */
		$sql = "
            SELECT
                Donation.ID as ID,
                Donation.post_date as date,
                Donation.post_status as status,
                DonationTotal.meta_value as total,
                DonorID.meta_value as donor_id,
                FormID.meta_value as form_id,
                FormTitle.meta_value as form_title,
                DonationCurrency.meta_value as currency,
                PaymentGateway.meta_value as gateway,
                DonorFirstName.meta_value as first_name,
				DonorLastName.meta_value as last_name,
				DonorEmail.email as email
            FROM {$wpdb->prefix}posts as Donation
            JOIN {$wpdb->prefix}give_donationmeta as DonationMode
                ON Donation.ID = DonationMode.donation_id
                AND DonationMode.meta_key = '_give_payment_mode'
                AND DonationMode.meta_value = '{$this->testMode}'
            JOIN {$wpdb->prefix}give_donationmeta as DonationTotal
                ON Donation.ID = DonationTotal.donation_id
                AND DonationTotal.meta_key = '_give_payment_total'
            JOIN {$wpdb->prefix}give_donationmeta as DonorID
                ON Donation.ID = DonorID.donation_id
                AND DonorID.meta_key = '_give_payment_donor_id'
            JOIN {$wpdb->prefix}give_donationmeta as FormID
                ON Donation.ID = FormID.donation_id
                AND FormID.meta_key = '_give_payment_form_id'
            JOIN {$wpdb->prefix}give_donationmeta as FormTitle
                ON Donation.ID = FormTitle.donation_id
                AND FormTitle.meta_key = '_give_payment_form_title'
            JOIN {$wpdb->prefix}give_donationmeta as DonationCurrency
                ON Donation.ID = DonationCurrency.donation_id
                AND DonationCurrency.meta_key = '_give_payment_currency'
                AND DonationCurrency.meta_value = '{$this->currency}'
            JOIN {$wpdb->prefix}give_donationmeta as PaymentGateway
                ON Donation.ID = PaymentGateway.donation_id
                AND PaymentGateway.meta_key = '_give_payment_gateway'
            JOIN {$wpdb->prefix}give_donormeta as DonorFirstName
                ON DonorID.meta_value = DonorFirstName.donor_id
                AND DonorFirstName.meta_key = '_give_donor_first_name'
            JOIN {$wpdb->prefix}give_donormeta as DonorLastName
                ON DonorID.meta_value = DonorLastName.donor_id
                AND DonorLastName.meta_key = '_give_donor_last_name'
			JOIN {$wpdb->prefix}give_donors as DonorEmail
                ON DonorID.meta_value = DonorEmail.id
            WHERE Donation.post_type = 'give_payment'
        ";

		if ( $this->startDate && $this->endDate ) {
			$sql .= " AND DATE( Donation.post_date ) BETWEEN '{$this->startDate}' AND '{$this->endDate}'";
		}

		if ( -1 !== $this->number ) {
			$sql .= " LIMIT {$this->number}";
		}

		return $sql;
	}
}
