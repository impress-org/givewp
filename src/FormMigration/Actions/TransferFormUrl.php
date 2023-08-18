<?php

namespace Give\FormMigration\Actions;

class TransferFormUrl
{
    protected $sourceId;

    public function __construct($sourceId)
    {
        $this->sourceId = $sourceId;
    }

    public static function from($sourceId): self
    {
        return new TransferFormUrl($sourceId);
    }

    public function to($destinationId): void
    {
        $this->__invoke($destinationId);
    }

    public function __invoke($destinationId)
    {
        $postName = get_post($this->sourceId)->post_name;
        wp_update_post(['ID' => $this->sourceId, 'post_name' => $postName . '-v2']);
        wp_update_post(['ID' => $destinationId, 'post_name' => $postName]);
    }
}
