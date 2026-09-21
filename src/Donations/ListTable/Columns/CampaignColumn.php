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
     * @since TBD Show the form title when the donation has no campaign or its campaign no longer exists.
     * @since 4.8.0 Added class to link
     * @since 4.3.0
     *
     * @inheritDoc
     *
     * @param Donation $model
     */
    public function getCellValue($model): string
    {
        $campaign = $model->campaignId ? $model->campaign : null;

        if ( ! $campaign) {
            return esc_html($model->formTitle);
        }

        return sprintf(
            '<a href="%s" aria-label="%s" class="campaignLink">%s</a>',
            admin_url("edit.php?post_type=give_forms&page=give-campaigns&id={$campaign->id}&tab=overview&action=edit"),
            __('Visit campaign page', 'give'),
            esc_html($campaign->title)
        );
    }
}
