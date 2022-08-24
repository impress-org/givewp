<?php

declare(strict_types=1);

namespace Give\Donations\DonationsListTable\Columns;

use Give\Framework\ListTable\AdvancedColumn;
use Give\Framework\QueryBuilder\QueryBuilder;

class StatusColumn extends AdvancedColumn
{
    public function getId(): string
    {
        return 'status';
    }

    public function getLabel(): string
    {
        return __('Status', 'give');
    }

    public function modifyQuery(QueryBuilder $query)
    {
        $query->select(['post_status', 'status']);
    }

    public function getSortingKey(): string
    {
        return 'status';
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
        return $row->status;
    }
}
