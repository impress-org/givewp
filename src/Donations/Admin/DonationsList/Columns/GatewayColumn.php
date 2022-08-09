<?php

declare(strict_types=1);

namespace Give\Donations\Admin\DonationsList\Columns;

use Give\Donations\ValueObjects\DonationMetaKeys;
use Give\Framework\ListTable\AdvancedColumn;
use Give\Framework\QueryBuilder\QueryBuilder;

class GatewayColumn extends AdvancedColumn
{
    /**
     * @inheritDoc
     */
    public function getId(): string
    {
        return 'gateway';
    }

    /**
     * @inheritDoc
     */
    public function getLabel(): string
    {
        return __('Gateway', 'give');
    }

    /**
     * @inheritDoc
     */
    public function modifyQuery(QueryBuilder $query)
    {
        $query->attachMeta('give_donationmeta', 'id', 'donation_id', DonationMetaKeys::GATEWAY);
    }

    /**
     * @inheritDoc
     */
    public function getSortingKey()
    {
        return DonationMetaKeys::GATEWAY;
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
        return give_get_gateway_admin_label($row->{DonationMetaKeys::GATEWAY});
    }
}
