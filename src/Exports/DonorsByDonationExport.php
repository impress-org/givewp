<?php

namespace Give\Exports;

use Give\Framework\Database\DB;

/**
 * @unreleased
 */
class DonorsByDonationExport extends \Give_Batch_Export {

    /**
     * @inheritdoc
     */
    public $export_type = 'donors_by_donation';

    /**
     * @inheritdoc
     */
    protected $posted_data;

    /**
     * @inheritdoc
     */
    public function set_properties( $posted_data ) {
        $this->posted_data = $posted_data;
    }

    /**
     * @inheritdoc
     */
    public function csv_cols() {
        return [
            'full_name'          => __( 'Name', 'give' ),
            'email'              => __( 'Email', 'give' ),
            'address_line1'      => __( 'Address', 'give' ),
            'address_line2'      => __( 'Address 2', 'give' ),
            'address_city'       => __( 'City', 'give' ),
            'address_state'      => __( 'State', 'give' ),
            'address_zip'        => __( 'Zip', 'give' ),
            'address_country'    => __( 'Country', 'give' ),
            'userid'             => __( 'User ID', 'give' ),
            'donations'          => __( 'Number of donations', 'give' ),
            'donation_sum'       => __( 'Total Donated', 'give' ),
        ];
    }

    /**
     * @inheritdoc
     */
    public function get_data() {

        $donorsTable = DB::prefix('give_donors');
        $donationsTable = DB::prefix('posts');
        $donationMetaTable = DB::prefix('give_donationmeta');

        $query = "
            SELECT DISTINCT donors.* from {$donorsTable} AS donors
                 JOIN (SELECT donations.ID, meta.meta_value AS donorId from {$donationsTable} AS donations
                        JOIN {$donationMetaTable} AS meta ON donations.ID = meta.donation_id AND meta.meta_key = '_give_payment_donor_id'
                        JOIN {$donationMetaTable} AS metaFormID
                            ON donations.ID = metaFormID.donation_id
                                   AND metaFormID.meta_key = '_give_payment_form_id'
                                   {$this->getDonationFormWhereClause()}
                       WHERE donations.post_type = 'give_payment'
                         {$this->getDonationDateWhereClause()}
                     ) AS sub ON donors.id = sub.donorId
        ";

        $results = DB::get_results($query);

        $export_data = array_map(function( $donorData ) {
            // @TODO N+1
            $addressData = give_get_donor_address( $donorData->id );
            return [
                'full_name'          => $donorData->name,
                'email'              => $donorData->email,
                'address_line1'      => $addressData[ 'line1' ],
                'address_line2'      => $addressData[ 'line2' ],
                'address_city'       => $addressData[ 'city' ],
                'address_state'      => $addressData[ 'state' ],
                'address_zip'        => $addressData[ 'zip' ],
                'address_country'    => $addressData[ 'country' ],
                'userid'             => $donorData->user_id,
                'donor_created_date' => __( 'Donor Created Date', 'give' ),
                'donations'          => $donorData->purchase_count,
                'donation_sum'       => $donorData->purchase_value,
            ];
        }, $results );

        /**
         * @since 1.3.0
         * @param $export_data
         */
        $data = apply_filters( "give_export_get_data_{$this->export_type}", $export_data );

        return $data;
    }

    /**
     * @unreleased
     * @return string
     */
    protected function getDonationFormWhereClause()
    {
        return $this->posted_data['forms'] ? "AND metaFormID.meta_value = {$this->posted_data['forms']}" : '';
    }

    /**
     * @unreleased
     * @return string
     */
    protected function getDonationDateWhereClause()
    {
        $donationStartDate = date('Y-m-d', strtotime($this->posted_data['donors_by_donation_export_donation_start_date']));
        $donationEndDate = date('Y-m-d', strtotime($this->posted_data['donors_by_donation_export_donation_end_date']));

        if( $this->isValidDate( $this->posted_data['donors_by_donation_export_donation_start_date'] ) && $this->isValidDate( $this->posted_data['donors_by_donation_export_donation_end_date'] ) ) {
            return "AND donations.post_date BETWEEN '{$donationStartDate}' AND '{$donationEndDate}'";
        } elseif( $this->isValidDate( $this->posted_data['donors_by_donation_export_donation_start_date'] ) ) {
            return "AND donations.post_date >= '{$donationStartDate}'";
        } elseif( $this->isValidDate( $this->posted_data['donors_by_donation_export_donation_end_date'] ) ) {
            return "AND donations.post_date < '{$donationEndDate}'";
        } else {
            return '';
        }
    }

    /**
     * @unreleased
     * @param $date
     * @param $format
     * @return bool
     */
    protected function isValidDate($date, $format= 'Y-m-d'){
        return $date == date($format, strtotime($date));
    }
}
