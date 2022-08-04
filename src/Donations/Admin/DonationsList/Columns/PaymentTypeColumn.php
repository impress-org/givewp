<?php

declare(strict_types=1);

namespace Give\Donations\Admin\DonationsList\Columns;

use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\ListTable\AdvancedColumn;
use Give\Framework\ListTable\SimpleColumn;
use Give\Donations\ValueObjects\DonationMetaKeys;
use Give\Framework\QueryBuilder\QueryBuilder;

class PaymentTypeColumn extends AdvancedColumn
{
    /**
     * @inheritDoc
     */
    public function getId(): string
    {
        return 'payment_type';
    }

    /**
     * @inheritDoc
     */
    public function getLabel(): string
    {
        return __('Payment Type', 'give');
    }

    /**
     * @inheritDoc
     */
    public function modifyQuery(QueryBuilder $query)
    {
        $query->attachMeta(
            'give_donationmeta',
            'id',
            'donation_id',
            DonationMetaKeys::SUBSCRIPTION_INITIAL_DONATION,
            DonationMetaKeys::SUBSCRIPTION_ID,
            DonationMetaKeys::IS_RECURRING
        );
    }

    /**
     * @inheritDoc
     */
    public function getColumnValue($value, $row): string
    {
        // The start of a subscription
        if ($row->{DonationMetaKeys::SUBSCRIPTION_INITIAL_DONATION}) {
            return 'subscription';
        }

        // Status is that of a renewal
        if (isset($row->status) && $row->status === DonationStatus::RENEWAL) {
            return 'renewal';
        }

        // Belongs to a subscription, so a renewal
        if (!empty($row->{DonationMetaKeys::SUBSCRIPTION_ID})) {
            return 'renewal';
        }

        // Is marked as recurring, so a renewal
        if (!empty($row->{DonationMetaKeys::IS_RECURRING})) {
            return 'renewal';
        }

        // If none of the above is true, then it's a one-time donation
        return 'single';
    }

    /**
     * @inheritDoc
     */
    public function getSortingKey()
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function isSortable(): bool
    {
        return false;
    }
}
