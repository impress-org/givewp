<?php

declare(strict_types=1);

namespace Give\Donations\Admin\DonationsList\Columns;

use Give\Donations\ValueObjects\DonationMetaKeys;
use Give\Framework\ListTable\AdvancedColumn;
use Give\Framework\QueryBuilder\QueryBuilder;

class DonorColumn extends AdvancedColumn
{
    /**
     * @inheritDoc
     */
    public function getId(): string
    {
        return 'donor_name';
    }

    /**
     * @inheritDoc
     */
    public function getLabel(): string
    {
        return __('Donor Name', 'give');
    }

    /**
     * @inheritDoc
     */
    public function modifyQuery(QueryBuilder $query)
    {
        $query->attachMeta('give_donationmeta', 'id', 'donation_id', DonationMetaKeys::FIRST_NAME, DonationMetaKeys::LAST_NAME);
    }

    /**
     * @inheritDoc
     */
    public function getSortingKey()
    {
        return 'CONCAT(' . DonationMetaKeys::LAST_NAME . ', " ", ' . DonationMetaKeys::FIRST_NAME . ')';
    }

    /**
     * @inheritDoc
     */
    public function isSortable(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function getCellValue($row): string
    {
        return $row->{DonationMetaKeys::FIRST_NAME} . ' ' . $row->{DonationMetaKeys::LAST_NAME};
    }
}
