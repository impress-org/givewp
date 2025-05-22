<?php

declare(strict_types=1);

namespace Give\Donations\ListTable\Columns;

use Give\Donations\Models\Donation;
use Give\Framework\ListTable\ModelColumn;

/**
 * @since 4.3.0
 *
 * @extends ModelColumn<Donation>
 */
class CampaignColumn extends ModelColumn
{
    /**
     * @since 4.3.0
     *
     * @inheritDoc
     */
    public static function getId(): string
    {
        return 'campaign';
    }

    /**
     * @since 4.3.0
     *
     * @inheritDoc
     */
    public function getLabel(): string
    {
        return __('Campaign', 'give');
    }

    /**
     * @since 4.3.0
     *
     * @inheritDoc
     *
     * @param Donation $model
     */
    public function getCellValue($model): string
    {
        return sprintf(
            '<a href="%s" aria-label="%s">%s</a>',
            admin_url("edit.php?post_type=give_forms&page=give-campaigns&id={$model->campaign->id}&tab=overview&action=edit"),
            __('Visit campaign page', 'give'),
            $model->campaign->title
        );
    }
}
