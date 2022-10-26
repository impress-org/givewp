<?php

declare(strict_types=1);

namespace Give\Donations\ListTable\Columns;

use Give\Donations\Models\Donation;
use Give\Framework\ListTable\ModelColumn;

/**
 * @extends ModelColumn<Donation>
 */
class GatewayColumn extends ModelColumn
{
    public $sortColumn = 'gateway';

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
     *
     * @param Donation $model
     */
    public function getCellValue($model): string
    {
        return give_get_gateway_admin_label($model->gatewayId);
    }
}
