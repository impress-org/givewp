<?php

namespace Give\Framework\Receipts\Properties;

use Give\Framework\Support\Contracts\Arrayable;

class ReceiptSettings implements Arrayable
{

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
     * @since 3.0.0
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
     * @since 3.0.0
     */
    public function getSettings(): array
    {
        return $this->settings;
    }

    /**
     * @since 3.0.0
     */
    public function toArray(): array
    {
        return $this->settings;
    }
}