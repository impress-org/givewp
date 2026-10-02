<?php

use Give\Campaigns\Models\Campaign;

/**
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
<div data-givewp-campaign-block data-attributes="<?= esc_attr(json_encode($attributes)) ?>"></div>
