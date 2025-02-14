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
<div class="give-campaigns-campaignBlock-container" data-attributes=<?= json_encode($attributes) ?>></div>
