<?php

namespace Give\PaymentGateways\Gateways\TestGateway;

use Exception;
use Give\Donations\Models\Donation;
use Give\Donations\Models\DonationNote;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\PaymentGateways\Commands\RedirectOffsite;
use Give\Framework\PaymentGateways\Exceptions\PaymentGatewayException;
use Give\Framework\PaymentGateways\PaymentGateway;
use Give\Helpers\Form\Utils as FormUtils;
use Give\PaymentGateways\DataTransferObjects\GatewayPaymentData;
use Give\PaymentGateways\Gateways\TestGateway\Views\LegacyFormFieldMarkup;

use function Give\Framework\Http\Response\response;

/**
 * Class TestGatewayOffsite
 * @since 2.18.0
 */
class TestGatewayOffsite extends PaymentGateway
{
    /**
     * @inheritDoc
     */
    public $routeMethods = [
        'returnFromOffsiteRedirect'
    ];

    /**
     * @inheritDoc
     */
    public $secureRouteMethods = [
        'securelyReturnFromOffsiteRedirect'
    ];

    /**
     * @inheritDoc
     */
    public static function id()
    {
        return 'test-gateway-offsite';
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
        return __('Test Gateway Offsite', 'give');
    }

    /**
     * @inheritDoc
     */
    public function getPaymentMethodLabel()
    {
        return __('Test Gateway Offsite', 'give');
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
        $redirectUrl = $this->generateSecureGatewayRouteUrl(
            'securelyReturnFromOffsiteRedirect',
            $paymentData->donationId,
            ['give-donation-id' => $paymentData->donationId]
        );

        return new RedirectOffsite($redirectUrl);
    }

    /**
     * An example of using a routeMethod for extending the Gateway API to handle a redirect.
     *
     * @unreleased update to use Donation model
     * @since 2.19.0
     *
     * @param  array  $queryParams
     * @throws PaymentGatewayException
     * @throws Exception
     */
    public function returnFromOffsiteRedirect($queryParams)
    {
        $donation = Donation::find($queryParams['give-donation-id']);

        if (!$donation) {
            throw new PaymentGatewayException('Donation does not exist');
        }

        $this->updateDonation($donation);

        return response()->redirectTo(give_get_success_page_uri());
    }

    /**
     * An example of using a secureRouteMethod for extending the Gateway API to handle a redirect.
     *
     * @unreleased update to use Donation model
     * @since 2.19.0
     *
     * @param  array  $queryParams
     * @throws PaymentGatewayException
     * @throws Exception
     */
    public function securelyReturnFromOffsiteRedirect($queryParams)
    {
        $donation = Donation::find($queryParams['give-donation-id']);

        if (!$donation) {
            throw new PaymentGatewayException('Donation does not exist');
        }

        $this->updateDonation($donation);
        
        return response()->redirectTo(give_get_success_page_uri());
    }

    /**
     * @param  Donation  $donation
     * @return void
     * @throws Exception
     */
    private function updateDonation(Donation $donation)
    {
        $donation->status = DonationStatus::COMPLETE();
        $donation->gatewayTransactionId = "test-gateway-transaction-id";
        $donation->save();

        DonationNote::create([
            'donationId' => $donation->id,
            'content' => 'Donation Completed from Test Gateway Offsite.'
        ]);
    }
}
