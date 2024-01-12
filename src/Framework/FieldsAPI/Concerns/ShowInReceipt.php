<?php

namespace Give\Framework\FieldsAPI\Concerns;

use Closure;

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
     * @var string
     */
    protected $receiptLabel;

    /**
     * @var Closure
     */
    protected $receiptValueCallback;

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

    /**
     * @since 3.3.0
     */
    public function receiptLabel(string $label): self
    {
        $this->receiptLabel = $label;

        return $this;
    }

    /**
     * @since 3.3.0
     */
    public function hasReceiptLabel(): bool
    {
        return !empty($this->receiptLabel);
    }

    /**
     * @since 3.3.0
     */
    public function getReceiptLabel(): string
    {
        return $this->receiptLabel;
    }

    /**
     * @since 3.3.0
     */
    public function hasReceiptValue(): bool
    {
        return !empty($this->receiptValueCallback);
    }

    /**
     * @since 3.3.0
     */
    public function isReceiptValueCallback(): bool
    {
        return is_callable($this->receiptValueCallback);
    }

    /**
     * @since 3.3.0
     */
    public function getReceiptValue(): Closure
    {
        return $this->receiptValueCallback;
    }

    /**
     * @since 3.3.0
     */
    public function receiptValue(Closure $value): self
    {
        $this->receiptValueCallback = $value;

        return $this;
    }
}
