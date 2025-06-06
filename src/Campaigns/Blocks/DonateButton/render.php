<?php

use Give\Campaigns\Models\Campaign;
use Give\Campaigns\Repositories\CampaignRepository;

/**
 * @var array    $attributes
 * @var Campaign $campaign
 */

if (!isset($attributes['campaignId']) || !($campaign = give(CampaignRepository::class)->getById($attributes['campaignId']))) {
    return;
}

$blockInlineStyles = sprintf(
    '--givewp-primary-color: %s;',
    esc_attr($campaign->primaryColor)
);

$useDefaultForm = (bool)filter_var($attributes['useDefaultForm'], FILTER_VALIDATE_BOOLEAN);
$hasSelectedForm = isset($attributes['selectedForm']);
$selectedFormId = $hasSelectedForm ? (int)$attributes['selectedForm'] : null;
$formId = $useDefaultForm || ! $hasSelectedForm ? $campaign->defaultFormId : $selectedFormId;
$buttonText = esc_html($attributes['buttonText'] ?? __('Donate', 'give'));
$isEditor = defined('REST_REQUEST') && REST_REQUEST;
?>

<div <?php echo wp_kses_data(get_block_wrapper_attributes(['class' => 'givewp-campaign-donate-button-block', 'style' => esc_attr($blockInlineStyles)])); ?>>
    <?php
    ob_start();
    if ($isEditor) {
        echo sprintf(
            '<button type="button" class="givewp-donation-form-modal__open">%s</button>',
            esc_html($buttonText)
        );
    } else {
        echo give_form_shortcode([
        'id' => $formId,
        'campaign_id' => $campaign->id,
        'display_style' => 'modal',
        'continue_button_title' => $buttonText,
        'use_default_form' => $useDefaultForm,
        'button_color' => $campaign->primaryColor,
        'block_id' => $attributes['blockId'] ?? '',
        ]);
    }

    $final_output = ob_get_clean();
    echo $final_output;
    ?>
</div>
