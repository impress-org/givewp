<?php

namespace Give\Framework\PaymentGateways\CommandHandlers;

use Give\Framework\PaymentGateways\Commands\PaymentComplete;

class PaymentCompleteHandler extends PaymentHandler {
    protected function getPaymentStatus() { return 'complete'; }
}
