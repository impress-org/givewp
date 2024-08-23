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
     * @var int
     */
    protected $donationFormId;

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

        $this->donationFormId = (int)$this->postedData['forms'];
    }

    /**
     * @since 3.12.1 Include donor phone.
     * @since      2.29.0 Include donor created date
     * @since      2.21.2
     * @since 3.3.0 Filter donors by form ID
     */
    public function get_data(): array
    {
        $donorQuery = DB::table('give_donors', 'donors')
            ->distinct()
            ->select(
                ['donors.name', 'full_name'],
                ['donors.email', 'email'],
                ['donors.user_id', 'userid'],
                ['donors.date_created', 'donor_created_date'],
                ['donors.phone', 'donor_phone_number'],
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

        if ($this->searchBy === 'donor') {
            if ($this->startDate && $this->endDate) {
                $donorQuery->whereBetween('DATE(donors.date_created)', $this->startDate, $this->endDate);
            } elseif ($this->startDate) {
                $donorQuery->where('DATE(donors.date_created)', $this->startDate, '>=');
            } elseif ($this->endDate) {
                $donorQuery->where('DATE(donors.date_created)', $this->endDate, '<=');
            }
        } else {
            if ($this->startDate && $this->endDate) {
                $donationQuery->whereBetween('DATE(donations.post_date)', $this->startDate, $this->endDate);
            } elseif ($this->startDate) {
                $donationQuery->where('DATE(donations.post_date)', $this->startDate, '>=');
            } elseif ($this->endDate) {
                $donationQuery->where('DATE(donations.post_date)', $this->endDate, '<=');
            }
        }

        if ($this->donationFormId) {
            $donationQuery
                ->join(function (JoinQueryBuilder $builder) {
                    $builder
                        ->leftJoin('give_donationmeta', 'form')
                        ->on('donations.ID', 'form.donation_id')
                        ->andOn('form.meta_key', '_give_payment_form_id', true);
                })
                ->where('form.meta_value', $this->donationFormId);
        }

        $donorQuery->joinRaw("JOIN ({$donationQuery->getSQL()}) AS sub ON donors.id = sub.donorId");

        if ($this->shouldIncludeAddress()) {
            $donorQuery->attachMeta(
                'give_donormeta',
                'donors.ID',
                'donor_id',
                ['_give_donor_address_billing_line1_0', 'address_line1'],
                ['_give_donor_address_billing_line2_0', 'address_line2'],
                ['_give_donor_address_billing_city_0', 'address_city'],
                ['_give_donor_address_billing_state_0', 'address_state'],
                ['_give_donor_address_billing_zip_0', 'address_zip'],
                ['_give_donor_address_billing_country_0', 'address_country']
            );
        }

        return $this->filterExportData(
            array_map(function ($row) {
                return array_intersect_key($row, $this->csv_cols());
            }, $donorQuery->getAll(ARRAY_A))
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
    protected function filterExportData(array $exportData): array
    {
        /**
         * @since 2.21.2
         *
         * @param array $exportData
         */
        return apply_filters("give_export_get_data_{$this->export_type}", $exportData);
    }

    /**
     * @since 3.14.0
     */
    protected function filterColumnData(array $defaultColumns): array
    {
        /**
         * @since 3.14.0
         *
         * @param array $defaultColumns
         */
        return apply_filters('give_export_donors_get_default_columns', $defaultColumns );
    }

    /**
     * @since 3.14.0 allow cols to be filtered.
     * @since 3.12.1 Include donor_phone_number col.
     * @since      2.29.0 Include donor created col
     * @since      2.21.2
     */
    public function csv_cols(): array
    {
        $defaultColumns = [
            'full_name' => __('Name', 'give'),
            'email' => __('Email', 'give'),
            'userid' => __('User ID', 'give'),
            'donor_created_date' => __('Donor Created', 'give'),
            'donor_phone_number' => __('Donor Phone Number', 'give'),
            'donations' => __('Number of donations', 'give'),
            'donation_sum' => __('Total Donated', 'give'),
            'address' => [
                'address_line1' => __('Address', 'give'),
                'address_line2' => __('Address 2', 'give'),
                'address_city' => __('City', 'give'),
                'address_state' => __('State', 'give'),
                'address_zip' => __('Zip', 'give'),
                'address_country' => __('Country', 'give'),
            ],
        ];

        $defaultColumns = $this->flattenAddressColumn(
            array_intersect_key($defaultColumns, $this->postedData['give_export_columns'])
        );

        return $this->filterColumnData($defaultColumns);
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
}
