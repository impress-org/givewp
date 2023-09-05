<?php

namespace Give\FormMigration\DataTransferObjects;

use WP_REST_Request;

class TransferOptions
{
    /** @var bool */
    protected $delete;

    public function __construct(bool $delete)
    {
        $this->delete = $delete;
    }

    public static function fromRequest(WP_REST_Request $request): self
    {
        return new self(
            $request->get_param('changeUrl')
        );
    }

    public static function fromArray($options): self
    {
        return new self(
            $options['changeUrl']
        );
    }

    public function shouldDelete(): bool
    {
        return $this->delete;
    }
}
