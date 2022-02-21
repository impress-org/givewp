<?php

namespace Give\Exports;

use DateTime;
use Give\Framework\Database\DB;
use Give\Framework\QueryBuilder\JoinQueryBuilder;
use Give_Batch_Export;

/**
 * @unreleased
 */
class DonorsExport extends Give_Batch_Export
{
    /** @inheritdoc */
    public $export_type = 'donors';

    /** @inheritdoc */
    protected $posted_data;

    /** @var DateTime */
    protected $startDate;

    /** @var DateTime */
    protected $endDate;

    /**
     * @inheritdoc
     */
    public function set_properties( $posted_data ) {
        $this->posted_data = $posted_data;
        ray( $this->posted_data );

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
        return $this->flattenAddressColumn(
            $this->filterIncludedColumns([
                'full_name' => __( 'Name', 'give' ),
                'email' => __( 'Email', 'give' ),
                'address' => [
                    'address_line1'      => __( 'Address', 'give' ),
                    'address_line2'      => __( 'Address 2', 'give' ),
                    'address_city'       => __( 'City', 'give' ),
                    'address_state'      => __( 'State', 'give' ),
                    'address_zip'        => __( 'Zip', 'give' ),
                    'address_country'    => __( 'Country', 'give' ),
                ],
                'userid' => __( 'User ID', 'give' ),
                'donations' => __( 'Number of donations', 'give' ),
                'donation_sum' => __( 'Total Donated', 'give' ),
        ]));
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

        /*
         * @TODO Toggle filter by donor registration date and donor's donation dates.
         */

        if( $this->startDate && $this->endDate ) {
            $donationQuery->whereBetween('donations.post_date', $this->startDate, $this->endDate );
        } elseif( $this->startDate ) {
            $donationQuery->where('donations.post_date', $this->startDate, '>=');
        } elseif( $this->endDate ) {
            $donationQuery->where('donations.post_date', $this->endDate, '<');
        }

        $donorQuery->joinRaw( "JOIN ({$donationQuery->getSQL()}) AS sub ON donors.id = sub.donorId" );

        $results = DB::get_results($donorQuery->getSQL(), ARRAY_A );

        $exportData = array_map([$this, 'mapDonorColumnNames'], $results);

        if( $this->shouldIncludeAddress() ) {
            $exportData = array_map([$this, 'mapDonorAddress'], $exportData);
        }

        $exportData = array_map([$this, 'filterIncludedColumns'], $exportData );
        $exportData = array_map([$this, 'flattenAddressColumn'], $exportData );

        ray( $exportData );

        return $this->filterExportData( $exportData );
    }

    /**
     * @param array $donorData
     * @return array
     */
    protected function mapDonorColumnNames( $donorData )
    {
        return [
            'full_name'          => $donorData[ 'name' ],
            'email'              => $donorData[ 'email' ],
            'userid'             => $donorData[ 'user_id' ],
            'donations'          => $donorData[ 'purchase_count' ],
            'donation_sum'       => $donorData[ 'purchase_value' ],
        ];
    }

    /**
     * @return bool
     */
    protected function shouldIncludeAddress()
    {
        return isset( $this->posted_data[ 'give_export_columns' ][ 'address' ] );
    }

    /**
     * @param array $columnarData
     * @return array
     */
    protected function flattenAddressColumn( $columnarData )
    {
        return $this->flattenColumn( $columnarData, 'address' );
    }

    /**
     * @param array $columnarData
     * @param string $columnName
     * @return array
     */
    protected function flattenColumn( $columnarData, $columnName )
    {
        if( isset( $columnarData[ $columnName ])) {
            $columnarData = array_merge( $columnarData, $columnarData[ $columnName ] );
            unset( $columnarData[ $columnName ] );
        }
        return $columnarData;
    }

    /**
     * @TODO N+1 when querying for each donor's address.
     * @param array $donorData
     * @return array
     */
    protected function mapDonorAddress( $donorData )
    {
        $addressData = give_get_donor_address( $donorData[ 'id' ] );
        return array_merge( $donorData, [
            'address' => [
                'address_line1'      => $addressData[ 'line1' ],
                'address_line2'      => $addressData[ 'line2' ],
                'address_city'       => $addressData[ 'city' ],
                'address_state'      => $addressData[ 'state' ],
                'address_zip'        => $addressData[ 'zip' ],
                'address_country'    => $addressData[ 'country' ],
            ]
        ]);
    }

    /**
     * @param array $columnarData
     * @return array
     */
    protected function filterIncludedColumns( $columnarData )
    {
        return array_intersect_key( $columnarData, $this->posted_data[ 'give_export_columns' ] );
    }

    /**
     * @param array $exportData
     * @return array
     */
    protected function filterExportData( $exportData )
    {
        /**
         * @unreleased
         * @param $exportData
         */
        return apply_filters( "give_export_get_data_{$this->export_type}", $exportData );
    }
}
