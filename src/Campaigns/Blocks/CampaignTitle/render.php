<?php

use Give\Campaigns\Models\Campaign;
use Give\Campaigns\Repositories\CampaignRepository;



$headingLevel = isset($attributes['headingLevel']) ? (int) $attributes['headingLevel'] : 1;
$headingTag = 'h' . min(6, max(1, $headingLevel));

$textAlignClass = isset($attributes['textAlign']) ? 'has-text-align-' . $attributes['textAlign'] : '';
?>

<<?php
echo $headingTag; ?> <?php
echo wp_kses_data(get_block_wrapper_attributes(['class' => $textAlignClass])); ?>>
<?php echo esc_html($campaign->title); ?>
</<?php echo $headingTag; ?>>
