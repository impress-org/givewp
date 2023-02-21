<?php

namespace Give\WPCom\DataTransferObjects;

class LicensePayload
{
    /**
     * @var string
     */
    public $license;

    /**
     * @var string;
     */
    public $productSlug;

    public static function fromArray(array $array): self
    {
        $payload = new self();
        $payload->license = $array['license'];
        $payload->productSlug = $array['productSlug'];

        return $payload;
    }
}
