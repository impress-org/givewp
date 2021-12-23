<?php

namespace Give\Framework\PaymentGateways\CommandHandlers;

class PaymentAbandonedHandler extends PaymentHandler {
    protected function getPaymentStatus() { return 'abandoned'; }
}
