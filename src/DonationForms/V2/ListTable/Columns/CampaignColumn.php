<?php

declare(strict_types=1);

namespace Give\DonationForms\V2\ListTable\Columns;

use Give\DonationForms\Repositories\DonationFormDataRepository;
use Give\DonationForms\V2\Models\DonationForm;
use Give\Framework\ListTable\ModelColumn;

/**
 * @since TBD
 *
 * @extends ModelColumn<DonationForm>
 */
class CampaignColumn extends ModelColumn
{
    protected $useData = true;

    /**
     * @since TBD
     *
     * @inheritDoc
     */
    public static function getId(): string
    {
        return 'campaign';
    }

    /**
     * @since TBD
     *
     * @inheritDoc
     */
    public function getLabel(): string
    {
        return __('Campaign', 'give');
    }

    /**
     * @since TBD
     *
     * @inheritDoc
     *
     * @param DonationForm $model
     */
    public function getCellValue($model): string
    {
        /** @var DonationFormDataRepository $donationFormData */
        $donationFormData = $this->getListTableData();
        $campaign = $donationFormData->getCampaign($model);

        if ( ! $campaign) {
            return __('No campaign', 'give');
        }

        return sprintf(
            '<a href="%s" aria-label="%s" class="campaignLink">%s</a>',
            admin_url("edit.php?post_type=give_forms&page=give-campaigns&id={$campaign->id}&tab=overview&action=edit"),
            __('Visit campaign page', 'give'),
            esc_html($campaign->title)
        );
    }
}
