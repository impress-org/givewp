<?php
namespace Give\Framework\PaymentGateways\Commands;
/***
 * @unreleased
 */
class SubscriptionComplete implements GatewayCommand {
    /**
     * @var string
     */
    public $transactionId;
    /**
     * @var string
     */
    public $profileId;

    /**
     * @unreleased
     *
     * @param string $transactionId
     * @param string $profileId
     */
    public function __construct($transactionId, $profileId) {
        $this->transactionId = $transactionId;
        $this->profileId = $profileId;
    }
}