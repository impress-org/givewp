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

    /** @var String */
    protected $searchBy;

    /**
     * @inheritdoc
     */
    public function set_properties( $posted_data ) {
        $this->posted_data = $posted_data;

        if( $this->posted_data['start_date'] ) {
            $this->startDate = date('Y-m-d', strtotime($this->posted_data['start_date']));
        }

        if( $this->posted_data['end_date'] ) {
            $this->endDate = date('Y-m-d', strtotime($this->posted_data['end_date']));
        }

        if( $this->posted_data['search_by'] ) {
            $this->searchBy = $this->posted_data['search_by'];
        }
    }

    /**
     * @inheritdoc
     */
    public function csv_cols() {
        return $this->flattenAddressColumn(
            array_intersect_key([
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
        ], $this->posted_data[ 'give_export_columns' ] ));
    }

    /**
     * @inheritdoc
     */
    public function get_data()
    {
        $donorQuery = DB::table('give_donors', 'donors')
            ->distinct()
            ->select(
                [ 'donors.name', 'full_name' ],
                [ 'donors.email', 'email' ],
                [ 'donors.user_id', 'userid' ],
                [ 'donors.purchase_count', 'donations' ],
                [ 'donors.purchase_value', 'donation_sum' ]
            );

        $donationQuery = DB::table('posts', 'donations')
            ->select('donations.ID', [ 'meta.meta_value', 'donorId'])
            ->join(function(JoinQueryBuilder $builder) {
                $builder
                    ->leftJoin('give_donationmeta', 'meta')
                    ->on('donations.ID', 'meta.donation_id')
                    ->andOn('meta.meta_key', '_give_payment_donor_id', true);
            })
            ->where('donations.post_type', 'give_payment');

        if($this->searchBy === 'donor') {
            if( $this->startDate && $this->endDate ) {
                $donorQuery->whereBetween('donors.date_created', $this->startDate, $this->endDate );
            } elseif( $this->startDate ) {
                $donorQuery->where('donors.date_created', $this->startDate, '>=');
            } elseif( $this->endDate ) {
                $donorQuery->where('donors.date_created', $this->endDate, '<');
            }
        }
        else {
            if( $this->startDate && $this->endDate ) {
                $donationQuery->whereBetween('donations.post_date', $this->startDate, $this->endDate );
            } elseif( $this->startDate ) {
                $donationQuery->where('donations.post_date', $this->startDate, '>=');
            } elseif( $this->endDate ) {
                $donationQuery->where('donations.post_date', $this->endDate, '<');
            }
        }

        $donorQuery->joinRaw( "JOIN ({$donationQuery->getSQL()}) AS sub ON donors.id = sub.donorId" );

        if( $this->shouldIncludeAddress() ) {
            $donorQuery->attachMeta('give_donormeta',
                'donors.ID',
                'donor_id',
                [ '_give_donor_address_billing_line1_0', 'address_line1' ],
                [ '_give_donor_address_billing_line2_0', 'address_line2' ],
                [ '_give_donor_address_billing_city_0', 'address_city' ],
                [ '_give_donor_address_billing_state_0', 'address_state' ],
                [ '_give_donor_address_billing_country_0', 'address_country' ],
                [ '_give_donor_address_billing_zip_0', 'address_zip' ]
            );
        }

        return $this->filterExportData(
            array_map(function( $row ) {
                return array_intersect_key( $row, $this->csv_cols() );
            }, $donorQuery->getAll(ARRAY_A) )
        );
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
