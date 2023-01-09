<?php

namespace Give\NextGen\Framework\Receipts\Properties;

use Give\Framework\Support\Contracts\Arrayable;

class ReceiptSettings implements Arrayable {

    /**
     * @var array
     */
    protected $settings;

    /**
     * @param  array  $settings
     */
    public function __construct(array $settings = []) {
        $this->settings = $settings;
    }

    /**
     * @unreleased
     *
     * @param  string  $key
     * @param $value
     * @return $this
     */
    public function addSetting(string $key, $value): ReceiptSettings
    {
        $this->settings[$key] = $value;

        return $this;
    }

    /**
     * @unreleased
     */
    public function getSettings(): array
    {
        return $this->settings;
    }

    /**
     * @unreleased
     */
    public function toArray(): array
    {
        return $this->settings;
    }
}