<?php

namespace Give\Framework\PaymentGateways\CommandHandlers;

class PaymentRefundedHandler extends PaymentHandler {
    protected function getPaymentStatus() { return 'refunded'; }
}
