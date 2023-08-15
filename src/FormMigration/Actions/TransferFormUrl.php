<?php

namespace Give\FormMigration\Actions;

use Give\FormMigration\Contracts\TransferAction;

class TransferFormUrl extends TransferAction
{
    public function __invoke($destinationId)
    {
        $postName = get_post($this->sourceId)->post_name;
        wp_update_post(['ID' => $this->sourceId, 'post_name' => $postName . '-v2']);
        wp_update_post(['ID' => $destinationId, 'post_name' => $postName]);
    }
}
