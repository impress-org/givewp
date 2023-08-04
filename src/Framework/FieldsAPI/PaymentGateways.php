<?php

declare(strict_types=1);

namespace Give\Framework\FieldsAPI;

class PaymentGateways extends Field {

    /**
     * @unreleased
     *
     * @type bool
     */
    public $isTestMode;

    /**
     * @unreleased
     */
    public function testMode( bool $isTestMode = true ): PaymentGateways {
        $this->isTestMode = $isTestMode;

        return $this;
    }

    const TYPE = 'gateways';
}
