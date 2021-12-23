<?php

namespace Give\Framework\PaymentGateways\CommandHandlers;

use Give\Framework\PaymentGateways\Commands\PaymentProcessing;

class PaymentProcessingHandler extends PaymentHandler {
    protected function getPaymentStatus() { return 'processing'; }
}
