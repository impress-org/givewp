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
    public static function id(): string
    {
        return 'test-gateway-next-gen';
    }

    /**
     * @inheritDoc
     */
    public function getId(): string
    {
        return self::id();
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return __('Test Gateway Next Gen', 'give');
    }

    /**
     * @inheritDoc
     */
    public function getPaymentMethodLabel(): string
    {
        return __('Test Gateway Next Gen', 'give');
    }

    /**
     * @inheritDoc
     */
    public function getLegacyFormFieldMarkup(int $formId, array $args): string
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
    public function createPayment(Donation $donation)
    {
        $transactionId = "test-gateway-transaction-id-{$donation->id}";

        give_update_payment_status($donation->id);

        give_set_payment_transaction_id($donation->id, $transactionId);

        //return new PaymentComplete();

        return new RespondToBrowser([
            'donationId' => $donation->id,
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
