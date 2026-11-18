<?php

declare(strict_types=1);

namespace Give\DonationForms\V2\ListTable\Columns;

use Give\Campaigns\ValueObjects\CampaignType;
use Give\DonationForms\Repositories\DonationFormDataRepository;
use Give\DonationForms\V2\Models\DonationForm;
use Give\Framework\ListTable\ModelColumn;

/**
 * @since 4.19.0
 *
 * @extends ModelColumn<DonationForm>
 */
class CampaignColumn extends ModelColumn
{
    protected $useData = true;

    /**
     * Prefixed so the givewp_list_table_cell_value_{id} filter does not collide with the donations
     * campaign column, whose filters expect a Donation.
     *
     * @since 4.19.0
     *
     * @inheritDoc
     */
    public static function getId(): string
    {
        return 'formCampaign';
    }

    /**
     * @since 4.19.0
     *
     * @inheritDoc
     */
    public function getLabel(): string
    {
        return __('Campaign', 'give');
    }

    /**
     * @since TBD Drop the aria-label so the link is named by its visible text.
     * @since 4.19.0
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

        /* Only core campaigns have a page under give-campaigns. Add-ons link their own through the cell filter. */
        if (CampaignType::CORE !== $campaign->type) {
            return esc_html($campaign->title);
        }

        return sprintf(
            '<a href="%s" class="campaignLink">%s</a>',
            admin_url("edit.php?post_type=give_forms&page=give-campaigns&id={$campaign->id}&tab=overview&action=edit"),
            esc_html($campaign->title)
        );
    }
}
