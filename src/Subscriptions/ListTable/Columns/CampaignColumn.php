<?php

declare(strict_types=1);

namespace Give\Subscriptions\ListTable\Columns;

use Give\Framework\ListTable\ModelColumn;
use Give\Subscriptions\Models\Subscription;

/**
 * @unreleased
 *
 * @extends ModelColumn<Subscription>
 */
class CampaignColumn extends ModelColumn
{
    protected $sortColumn = 'campaignId';

    /**
     * @unreleased
     *
     * @inheritDoc
     */
    public static function getId(): string
    {
        return 'subscriptionCampaign';
    }

    /**
     * @unreleased
     *
     * @inheritDoc
     */
    public function getLabel(): string
    {
        return __('Campaign', 'give');
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
        ds($model);
        $campaign = give()->campaigns->getById($model->campaignId);

        if ( ! $campaign) {
            return sprintf( __( 'Campaign #%d', 'give' ), $model->campaignId );
        }

        return sprintf(
            '<a href="%s" aria-label="%s" class="campaignLink">%s</a>',
            admin_url("edit.php?post_type=give_forms&page=give-campaigns&id={$model->campaign->id}&tab=overview&action=edit"),
            __('Visit campaign page', 'give'),
            $model->campaign->title
        );
    }
}
