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
     * @unreleased
     */
    public function receiptLabel(string $label): self
    {
        $this->receiptLabel = $label;

        return $this;
    }

    /**
     * @unreleased
     */
    public function hasReceiptLabel(): bool
    {
        return !empty($this->receiptLabel);
    }

    /**
     * @unreleased
     */
    public function getReceiptLabel(): string
    {
        return $this->receiptLabel;
    }

    /**
     * @unreleased
     */
    public function hasReceiptValue(): bool
    {
        return !empty($this->receiptValueCallback);
    }

    /**
     * @unreleased
     */
    public function getReceiptValue(): Closure
    {
        return $this->receiptValueCallback;
    }

    /**
     * @unreleased
     */
    public function receiptValue(Closure $value): self
    {
        $this->receiptValueCallback = $value;

        return $this;
    }
}
