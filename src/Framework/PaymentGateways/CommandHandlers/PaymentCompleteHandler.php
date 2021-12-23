<?php

namespace Give\Framework\PaymentGateways\CommandHandlers;

class PaymentCompleteHandler extends PaymentHandler {
    protected function getPaymentStatus() { return 'complete'; }
}
