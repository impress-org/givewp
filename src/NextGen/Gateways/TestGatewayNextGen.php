<?php
namespace Give\NextGen\Gateways;

use Give\Donations\Models\Donation;
use Give\Framework\FieldsAPI\Contracts\Node;
use Give\Framework\FieldsAPI\Group;
use Give\Framework\PaymentGateways\Commands\RespondToBrowser;
use Give\Framework\PaymentGateways\PaymentGateway;
use Give\Helpers\Form\Utils as FormUtils;
use Give\PaymentGateways\DataTransferObjects\GatewayPaymentData;
use Give\PaymentGateways\Gateways\TestGateway\Views\LegacyFormFieldMarkup;

/**
 * @unreleased
 */
class TestGatewayNextGen extends PaymentGateway
{
    /**
     * @inheritDoc
     */
    public static function id()
    {
        return 'test-gateway-next-gen';
    }

    /**
     * @inheritDoc
     */
    public function getId()
    {
        return self::id();
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return __('Test Gateway Next Gen', 'give');
    }

    /**
     * @inheritDoc
     */
    public function getPaymentMethodLabel()
    {
        return __('Test Gateway Next Gen', 'give');
    }

    /**
     * @inheritDoc
     */
    public function getLegacyFormFieldMarkup($formId, $args)
    {
        if (FormUtils::isLegacyForm($formId)) {
            return false;
        }

        /** @var LegacyFormFieldMarkup $legacyFormFieldMarkup */
        $legacyFormFieldMarkup = give(LegacyFormFieldMarkup::class);

        return $legacyFormFieldMarkup();
    }

    /**
     * @inheritDoc
     */
    public function createPayment(GatewayPaymentData $paymentData)
    {
        $transactionId = "test-gateway-transaction-id-{$paymentData->donationId}";

        give_update_payment_status($paymentData->donationId);

        give_set_payment_transaction_id($paymentData->donationId, $transactionId);

        //return new PaymentComplete();

        return new RespondToBrowser([
            'donationId' => $paymentData->donationId,
            'redirectUrl' => give_get_success_page_uri(),
            'status' => "Complete"
        ]);
    }

    /**
     * @return Node
     */
    public function getPaymentFields()
    {
        return Group::make($this->getId());
    }

    /**
     * @inerhitDoc
     */
    public function refundDonation(Donation $donation)
    {
        // TODO: Implement refundDonation() method.
    }

}
