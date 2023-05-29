<?php

namespace Give\Framework\FieldsAPI\Concerns;

/**
 * @since 2.10.2
 */
trait StoreAsMeta
{

    /**
     * @since 2.10.2
     * @var bool
     */
    protected $storeAsDonorMeta = false;

    /**
     * @since 2.10.2
     * @return $this
     */
    public function storeAsDonorMeta(bool $storeAsDonorMeta = true): self
    {
        $this->storeAsDonorMeta = $storeAsDonorMeta;

        return $this;
    }

    /**
     * @since 2.10.2
     * @return bool
     */
    public function shouldStoreAsDonorMeta(): bool
    {
        return $this->storeAsDonorMeta;
    }
}
