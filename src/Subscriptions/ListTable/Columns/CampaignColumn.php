<?php

declare(strict_types=1);

namespace Give\Subscriptions\ListTable\Columns;

use Give\Framework\ListTable\ModelColumn;
use Give\Subscriptions\Models\Subscription;

/**
 * @since 4.12.0
 *
 * @extends ModelColumn<Subscription>
 */
class CampaignColumn extends ModelColumn
{
    protected $sortColumn = 'campaignId';

    /**
     * @since 4.12.0
     *
     * @inheritDoc
     */
    public static function getId(): string
    {
        return 'subscriptionCampaign';
    }

    /**
     * @since 4.12.0
     *
     * @inheritDoc
     */
    public function getLabel(): string
    {
        return __('Campaign', 'give');
    }

    /**
     * @since 4.12.0
     *
     * @inheritDoc
     *
     * @param Subscription $model
     */
    public function getCellValue($model): string
    {
        $campaign = give()->campaigns->getById($model->campaignId);

        if ( ! $campaign) {
            return sprintf( __( 'Campaign #%d', 'give' ), $model->campaignId );
        }

        return sprintf(
            '<a href="%s" aria-label="%s" class="campaignLink">%s</a>',
            admin_url("edit.php?post_type=give_forms&page=give-campaigns&id={$campaign->id}&tab=overview&action=edit"),
            __('Visit campaign page', 'give'),
            $campaign->title
        );
    }
}
