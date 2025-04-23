<?php

use Give\Campaigns\Models\Campaign;
use Give\Campaigns\Repositories\CampaignRepository;
use Give\DonationForms\Blocks\DonationFormBlock\Controllers\BlockRenderController;
use Give\Helpers\Frontend\ConfirmDonation;
use Give\Log\Log;
use Give\Views\IframeView;
use Give\Helpers\Form\Utils as FormUtils;

/**
 * @var array    $attributes
 * @var Campaign $campaign
 *
 * @unrelesed
 */

if (! isset($attributes['campaignId']) ||
    ! ($campaign = give(CampaignRepository::class)->getById($attributes['campaignId']))
) {
    return;
}

    ob_start();
    $atts = [
        'campaign_id'           => $attributes['campaignId'],
        'block_id'              => $attributes['blockId'] ?? '',
        'prev_id'               => $attributes['prevId'] ?? 0,
        'id'                    => $attributes['id'],
        'display_style'         => $attributes['displayStyle'] ?? 'onpage',
        'continue_button_title' => $attributes['continueButtonTitle'] ?? __('Donate Now', 'give'),
        'show_title'            => $attributes['showTitle'] ?? true,
        'content_display'       => $attributes['contentDisplay'] ?? 'above',
        'show_goal'             => $attributes['showGoal'] ?? true,
        'show_content'          => $attributes['showContent'] ?? true,
        'button_color'          => $campaign->primaryColor,
    ];

    echo give_form_shortcode($atts);

    $final_output = ob_get_clean();

    echo $final_output;
