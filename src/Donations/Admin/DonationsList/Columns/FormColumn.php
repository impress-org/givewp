<?php

declare(strict_types=1);

namespace Give\Donations\Admin\DonationsList\Columns;

use Give\Donations\ValueObjects\DonationMetaKeys;
use Give\Framework\ListTable\AdvancedColumn;
use Give\Framework\QueryBuilder\QueryBuilder;

class FormColumn extends AdvancedColumn
{
    /**
     * @inheritDoc
     */
    public function getId(): string
    {
        return 'form';
    }

    /**
     * @inheritDoc
     */
    public function getLabel(): string
    {
        return __('Donation Form', 'give');
    }

    /**
     * @inheritDoc
     */
    public function modifyQuery(QueryBuilder $query)
    {
        $query->attachMeta('give_donationmeta', 'id', 'donation_id', DonationMetaKeys::FORM_TITLE);
    }

    /**
     * @inheritDoc
     */
    public function getSortingKey()
    {
        return DonationMetaKeys::FORM_TITLE;
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
    public function getCellValue($row)
    {
        return $row->{DonationMetaKeys::FORM_TITLE};
    }
}
