<?php

declare(strict_types=1);

namespace Give\Donations\Admin\DonationsList\Columns;

use Give\Framework\ListTable\AdvancedColumn;
use Give\Framework\QueryBuilder\QueryBuilder;

class CreatedAtColumn extends AdvancedColumn
{
    /**
     * @inheritDoc
     */
    public function getId(): string
    {
        return 'created_at';
    }

    /**
     * @inheritDoc
     */
    public function getLabel(): string
    {
        return __('Created At', 'give');
    }

    /**
     * @inheritDoc
     */
    public function modifyQuery(QueryBuilder $query)
    {
        $query->select(['post_date', 'createdAt']);
    }

    /**
     * @inheritDoc
     */
    public function getSortingKey(): string
    {
        return 'createdAt';
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
        return $row->createdAt;
    }
}
