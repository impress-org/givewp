<?php

use Give\Campaigns\Models\Campaign;

/**
 * @since TBD Replace short echo tags with escaped echo.
 *
 * @var array    $attributes
 * @var Campaign $campaign
 */

if (
    ! isset($attributes['campaignId'])
    || ! Campaign::find($attributes['campaignId'])
) {
    return;
}

?>
<div data-givewp-campaign-block data-attributes="<?php echo esc_attr(json_encode($attributes)); ?>"></div>
