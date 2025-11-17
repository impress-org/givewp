<?php

use Give\Campaigns\Models\Campaign;
use Give\Campaigns\Repositories\CampaignRepository;

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
        'use_default_form'      => $attributes['useDefaultForm'] ?? true,
    ];

    if ($atts['use_default_form'] === true) {
        $atts['id'] = $campaign->defaultFormId;
    }

    echo give_form_shortcode($atts);

    $final_output = ob_get_clean();

    echo $final_output;
