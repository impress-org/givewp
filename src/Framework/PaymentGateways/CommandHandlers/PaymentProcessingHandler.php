<?php

namespace Give\Framework\PaymentGateways\CommandHandlers;

class PaymentProcessingHandler extends PaymentHandler {
    protected function getPaymentStatus() { return 'processing'; }
}
