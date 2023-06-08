<?php

namespace Give\Framework\FieldsAPI\Concerns;

/**
 * @since 2.10.2
 */
trait StoreAsMeta
{
    /**
     * @since 2.10.2
     */
    protected $storeAsDonorMeta = false;

    /**
     * @unreleased added types
     * @since 2.10.2
     */
    public function storeAsDonorMeta(bool $storeAsDonorMeta = true): self
    {
        $this->storeAsDonorMeta = $storeAsDonorMeta;

        return $this;
    }

    /**
     * @unreleased added types
     * @since 2.10.2
     */
    public function shouldStoreAsDonorMeta(): bool
    {
        return $this->storeAsDonorMeta;
    }
}
