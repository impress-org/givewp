<?php

declare(strict_types=1);

namespace Give\Framework\FieldsAPI;

class PaymentGateways extends Field {

    /**
     * @since 3.0.0
     *
     * @type bool
     */
    public $isTestMode;

    const TYPE = 'gateways';

    /**
     * @since 3.0.0
     */
    public function testMode( bool $isTestMode = true ): PaymentGateways {
        $this->isTestMode = $isTestMode;

        return $this;
    }
}
