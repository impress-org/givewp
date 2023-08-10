<?php

declare(strict_types=1);

namespace Give\Framework\FieldsAPI;

class PaymentGateways extends Field {

    /**
     * @0.6.0
     *
     * @type bool
     */
    public $isTestMode;

    /**
     * @0.6.0
     */
    public function testMode( bool $isTestMode = true ): PaymentGateways {
        $this->isTestMode = $isTestMode;

        return $this;
    }

    const TYPE = 'gateways';
}
