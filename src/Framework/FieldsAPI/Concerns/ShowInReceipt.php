<?php

namespace Give\Framework\FieldsAPI\Concerns;

/**
 * @since 2.10.2
 */
trait ShowInReceipt
{

    /**
     * @since 2.10.2
     */
    protected $showInReceipt = false;

    /**
     * @since 2.10.2
     */
    public function showInReceipt($showInReceipt = true): self
    {
        $this->showInReceipt = $showInReceipt;

        return $this;
    }

    /**
     * @since 2.10.2
     */
    public function shouldShowInReceipt(): bool
    {
        return $this->showInReceipt;
    }
}
