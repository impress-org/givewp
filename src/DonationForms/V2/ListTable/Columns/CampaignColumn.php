<?php

declare(strict_types=1);

namespace Give\DonationForms\V2\ListTable\Columns;

use Give\Campaigns\ValueObjects\CampaignType;
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
     * The id is prefixed with the entity because ListTable::safelyGetCellValue() builds its
     * givewp_list_table_cell_value_{id} filter from the column id alone, with no table in it. Sharing
     * "campaign" with the donations column would hand this table's models to filters written against
     * a Donation. Give\Subscriptions\ListTable\Columns\CampaignColumn uses "subscriptionCampaign" for
     * the same reason.
     *
     * @since TBD
     *
     * @inheritDoc
     */
    public static function getId(): string
    {
        return 'formCampaign';
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

        /*
         * Only core campaigns have a page under give-campaigns. A Peer-to-Peer campaign is reached
         * through its own add-on screen, so render its title unlinked and let the add-on supply the
         * link through givewp_list_table_cell_value_formCampaign.
         */
        if (CampaignType::CORE !== $campaign->type) {
            return esc_html($campaign->title);
        }

        return sprintf(
            '<a href="%s" aria-label="%s" class="campaignLink">%s</a>',
            admin_url("edit.php?post_type=give_forms&page=give-campaigns&id={$campaign->id}&tab=overview&action=edit"),
            __('Visit campaign page', 'give'),
            esc_html($campaign->title)
        );
    }
}
