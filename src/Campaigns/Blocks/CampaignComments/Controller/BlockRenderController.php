<?php

namespace Give\Campaigns\Blocks\CampaignComments\Controller;

use Give\Campaigns\Blocks\CampaignComments\DataTransferObjects\BlockAttributes;
use Give\Campaigns\CampaignDonationQuery;
use Give\Campaigns\Repositories\CampaignRepository;
use Give\Donations\ValueObjects\DonationMetaKeys;
use Give\Framework\Support\Facades\Scripts\ScriptAsset;

/**
 * @unreleased
 */
class BlockRenderController
{
    /**
     * @unreleased
     */
    public function render(array $attributes): string
    {
        $blockAttributes = BlockAttributes::fromArray($attributes);

        $encodedAttributes = json_encode($blockAttributes->toArray());

        $blockId = $blockAttributes->blockId;

        $this->loadScripts($blockAttributes);

        return "<div id='givewp-campaign-comments-block-{$blockId}' class='givewp-campaign-comment-block' data-attributes='{$encodedAttributes}'></div>";
    }

    /**
     * @unreleased
     */
    public function getCampaignCommentData($attributes): array
    {
        $data = [];
        $campaign = give(CampaignRepository::class)->getById($attributes->campaignId);
        $donations = $this->getCampaignDonationMeta($attributes, $campaign);

        foreach ($donations as $donation) {
            $data[] = [
                'campaignTitle' => $campaign->title,
                'anonymous'     => $donation->anonymous,
                'donorName'     => $donation->donorName,
                'comment'       => $donation->comment,
                'date'          => human_time_diff(strtotime($donation->date)),
                'avatar'        => get_avatar_url($donation->email),
            ];
        }

        return $data;
    }

    /**
     * @unreleased
     */
    public function getCampaignDonationMeta($attributes, $campaign)
    {
        $query = (new CampaignDonationQuery($campaign))
            ->joinDonationMeta(DonationMetaKeys::DONOR_ID, 'donorIdMeta')
            ->joinDonationMeta(DonationMetaKeys::COMMENT, 'commentMeta')
            ->joinDonationMeta(DonationMetaKeys::ANONYMOUS, 'anonymousMeta')
            ->joinDonationMeta('_give_completed_date', 'dateMeta')
            ->leftJoin('give_donors', 'donorIdMeta.meta_value', 'donors.id', 'donors');

        $query->select(
            'donorIdMeta.meta_value as donorId',
            'commentMeta.meta_value as comment',
            'anonymousMeta.meta_value as anonymous',
            'dateMeta.meta_value as date',
            'donors.name as donorName'
        );

        return $query->getAll();
    }

    /**
     * @unreleased
     */
    public function loadScripts($blockAttributes)
    {
        $handleName = 'givewp-campaign-comments-block';
        $scriptAsset = ScriptAsset::get(GIVE_PLUGIN_DIR . 'build/campaignCommentsBlockApp.asset.php');

        wp_register_script(
            $handleName,
            GIVE_PLUGIN_URL . 'build/campaignCommentsBlockApp.js',
            $scriptAsset['dependencies'],
            $scriptAsset['version'],
            true
        );

        wp_enqueue_script($handleName);

        wp_localize_script(
            $handleName,
            'GiveCampaignCommentsBlockWindowData',
            $this->getCampaignCommentData($blockAttributes)
        );

        wp_localize_script(
            'givewp-campaign-blocks',
            'GiveCampaignCommentsBlockWindowData',
            $this->getCampaignCommentData($blockAttributes)
        );
    }
}
