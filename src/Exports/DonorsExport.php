<?php

namespace Give\Exports;

use Give\Framework\Database\DB;
use Give\Framework\QueryBuilder\JoinQueryBuilder;
use Give_Batch_Export;

/**
 * @since 2.21.2
 */
class DonorsExport extends Give_Batch_Export
{
    /** @inheritdoc */
    public $export_type = 'donors';

    /** @var array */
    protected $postedData;

    /** @var String */
    protected $startDate;

    /** @var String */
    protected $endDate;

    /** @var String */
    protected $searchBy;

    /**
     * @inheritdoc
     */
    public function set_properties($request)
    {
        $this->postedData = $request;

        if ($this->postedData['giveDonorExport-startDate']) {
            $this->startDate = date('Y-m-d', strtotime($this->postedData['giveDonorExport-startDate']));
        }

        if ($this->postedData['giveDonorExport-endDate']) {
            $this->endDate = date('Y-m-d', strtotime($this->postedData['giveDonorExport-endDate']));
        }

        if ($this->postedData['searchBy']) {
            $this->searchBy = $this->postedData['searchBy'];
        }
    }

    /**
     * @since 2.21.2
     */
    public function csv_cols(): array
    {
        return $this->flattenAddressColumn(
            array_intersect_key([
                'full_name' => __('Name', 'give'),
                'email' => __('Email', 'give'),
                'address' => [
                    'address_line1' => __('Address', 'give'),
                    'address_line2' => __('Address 2', 'give'),
                    'address_city' => __('City', 'give'),
                    'address_state' => __('State', 'give'),
                    'address_zip' => __('Zip', 'give'),
                    'address_country' => __('Country', 'give'),
                ],
                'userid' => __('User ID', 'give'),
                'donations' => __('Number of donations', 'give'),
                'donation_sum' => __('Total Donated', 'give'),
            ], $this->postedData['give_export_columns'])
        );
    }

    /**
     * @since 2.21.2
     */
    public function get_data(): array
    {
        $donorQuery = DB::table('give_donors', 'donors')
            ->distinct()
            ->select(
                ['donors.name', 'full_name'],
                ['donors.email', 'email'],
                ['donors.user_id', 'userid'],
                ['donors.purchase_count', 'donations'],
                ['donors.purchase_value', 'donation_sum']
            );

        $donationQuery = DB::table('posts', 'donations')
            ->select('donations.ID', ['meta.meta_value', 'donorId'])
            ->join(function (JoinQueryBuilder $builder) {
                $builder
                    ->leftJoin('give_donationmeta', 'meta')
                    ->on('donations.ID', 'meta.donation_id')
                    ->andOn('meta.meta_key', '_give_payment_donor_id', true);
            })
            ->where('donations.post_type', 'give_payment');

        if($this->searchBy === 'donor') {
            if( $this->startDate && $this->endDate ) {
                $donorQuery->whereBetween('DATE(donors.date_created)', $this->startDate, $this->endDate);
            } elseif( $this->startDate ) {
                $donorQuery->where('DATE(donors.date_created)', $this->startDate, '>=');
            } elseif( $this->endDate ) {
                $donorQuery->where('DATE(donors.date_created)', $this->endDate, '<=');
            }
        }
        else {
            if( $this->startDate && $this->endDate ) {
                $donationQuery->whereBetween('DATE(donations.post_date)', $this->startDate, $this->endDate);
            } elseif( $this->startDate ) {
                $donationQuery->where('DATE(donations.post_date)', $this->startDate, '>=');
            } elseif( $this->endDate ) {
                $donationQuery->where('DATE(donations.post_date)', $this->endDate, '<=');
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
     * @since 2.21.2
     */
    protected function shouldIncludeAddress(): bool
    {
        return isset($this->postedData['give_export_columns']['address']);
    }

    /**
     * @since 2.21.2
     */
    protected function flattenAddressColumn(array $columnarData): array
    {
        return $this->flattenColumn($columnarData, 'address');
    }

    /**
     * @since 2.21.2
     */
    protected function flattenColumn(array $columnarData, string $columnName): array
    {
        if (isset($columnarData[$columnName])) {
            $columnarData = array_merge($columnarData, $columnarData[$columnName]);
            unset($columnarData[$columnName]);
        }
        return $columnarData;
    }

    /**
     * @since 2.21.2
     */
    protected function filterExportData(array $exportData): array
    {
        /**
         * @since 2.21.2
         * @param $exportData
         */
        return apply_filters("give_export_get_data_{$this->export_type}", $exportData);
    }
}
