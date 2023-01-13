<?php

declare(strict_types=1);

namespace Give\Subscriptions\ListTable\Columns;

use Give\Subscriptions\Models\Subscription;
use Give\Framework\ListTable\ModelColumn;

/**
 * @unreleased
 *
 * @extends ModelColumn<Subscription>
 */
class DonorColumn extends ModelColumn
{

    /**
     * @unreleased
     *
     * @inheritDoc
     */
    public static function getId(): string
    {
        return 'donor';
    }

    /**
     * @unreleased
     *
     * @inheritDoc
     */
    public function getLabel(): string
    {
        return __('Donor name', 'give');
    }

    /**
     * @unreleased
     *
     * @inheritDoc
     *
     * @param Subscription $model
     */
    public function getCellValue($model): string
    {
        $name = array_filter( [
            $model->donor->firstName,
            $model->donor->lastName,
        ] );

        return sprintf(
            '<a href="%s" aria-label="%s">%s</a>',
            admin_url("edit.php?post_type=give_forms&page=give-donors&view=overview&id=$model->donorId"),
            __('View donor information', 'give'),
            trim(implode(' ', $name))
        );
    }
}
