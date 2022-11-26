<?php

namespace Give\Subscriptions\ListTable;

use Give\Subscriptions\ListTable\Columns\IdColumn;
use Give\Framework\ListTable\ListTable;

/**
 * @unreleased
 */
class SubscriptionsListTable extends ListTable
{
    /**
     * @unreleased
     *
     * @inheritDoc
     */
    public function id(): string
    {
        return 'subscriptions';
    }

    /**
     * @unreleased
     *
     * @inheritDoc
     */
    public function getColumns(): array
    {
        return [
            new IdColumn(),
        ];
    }

    /**
     * @unreleased
     *
     * @inheritDoc
     */
    public function getDefaultVisibleColumns(): array
    {
        return [
            IdColumn::getId(),
        ];
    }
}
