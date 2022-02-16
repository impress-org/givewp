<?php

namespace Give\Exports;

use Give\Framework\Database\DB;
use Give\Framework\QueryBuilder\JoinQueryBuilder;
use Give_Batch_Export;

/**
 * @unreleased
 */
class DonorsByDonationExport extends Give_Batch_Export {

    /**
     * @inheritdoc
     */
    public $export_type = 'donors_by_donation';

    /**
     * @inheritdoc
     */
    protected $posted_data;

    protected $startDate;
    protected $endDate;

    /**
     * @inheritdoc
     */
    public function set_properties( $posted_data ) {
        $this->posted_data = $posted_data;

        if( ! $this->posted_data['start_date'] ) {
            $this->startDate = date('Y-m-d', strtotime($this->posted_data['start_date']));
        }

        if( $this->posted_data['end_date'] ) {
            $this->endDate = date('Y-m-d', strtotime($this->posted_data['end_date']));
        }

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
    public function get_data()
    {
        $donorQuery = DB::table('give_donors', 'donors')
            ->distinct()
            ->select('donors.*');

        $donationQuery = DB::table('posts', 'donations')
            ->select('donations.ID', [ 'meta.meta_value', 'donorId'])
            ->join(function(JoinQueryBuilder $builder) {
                $builder
                    ->leftJoin('give_donationmeta', 'meta')
                    ->on('donations.ID', 'meta.donation_id')
                    ->andOn('meta.meta_key', '_give_payment_donor_id', true);
            })
            ->where('donations.post_type', 'give_payment');

        if( $this->startDate && $this->endDate ) {
            $donationQuery->whereBetween('donations.post_date', $this->startDate, $this->endDate );
        } elseif( $this->startDate ) {
            $donationQuery->where('donations.post_date', $this->startDate, '>=');
        } elseif( $this->endDate ) {
            $donationQuery->where('donations.post_date', $this->endDate, '<');
        }

        $donorQuery->joinRaw( "JOIN ({$donationQuery->getSQL()}) AS sub ON donors.id = sub.donorId" );

        $results = DB::get_results($donorQuery->getSQL());

        $exportData = array_map(function( $donorData ) {
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
                'donations'          => $donorData->purchase_count,
                'donation_sum'       => $donorData->purchase_value,
            ];
        }, $results );

        return $this->filterExportData( $exportData );
    }

    protected function filterExportData( $exportData )
    {
        /**
         * @unreleased
         * @param $exportData
         */
        return apply_filters( "give_export_get_data_{$this->export_type}", $exportData );
    }
}
