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
     * @since 2.28.0 added types
     * @since 2.10.2
     */
    public function storeAsDonorMeta(bool $storeAsDonorMeta = true): self
    {
        $this->storeAsDonorMeta = $storeAsDonorMeta;

        return $this;
    }

    /**
     * @since 2.28.0 added types
     * @since 2.10.2
     */
    public function shouldStoreAsDonorMeta(): bool
    {
        return $this->storeAsDonorMeta;
    }
}
