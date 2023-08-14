<?php

declare(strict_types=1);

namespace Give\Framework\FieldsAPI;

class PaymentGateways extends Field {

    /**
     * @since 0.6.0
     *
     * @type bool
     */
    public $isTestMode;

    /**
     * @since 0.6.0
     */
    public function testMode( bool $isTestMode = true ): PaymentGateways {
        $this->isTestMode = $isTestMode;

        return $this;
    }

    const TYPE = 'gateways';
}
