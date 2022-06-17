<?php

namespace Give\PaymentGateways\Gateways\TestGateway;

use Give\Donations\Models\Donation;
use Give\Donations\Models\DonationNote;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\Http\Response\Types\RedirectResponse;
use Give\Framework\PaymentGateways\Commands\RedirectOffsite;
use Give\Framework\PaymentGateways\Exceptions\PaymentGatewayException;
use Give\Framework\PaymentGateways\PaymentGateway;
use Give\Helpers\Form\Utils as FormUtils;
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
    public static function id(): string
    {
        return 'test-gateway-offsite';
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
        return __('Test Gateway Offsite', 'give');
    }

    /**
     * @inheritDoc
     */
    public function getPaymentMethodLabel(): string
    {
        return __('Test Gateway Offsite', 'give');
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
    public function createPayment(Donation $donation, $gatewayData = null)
    {
        $redirectUrl = $this->generateSecureGatewayRouteUrl(
            'securelyReturnFromOffsiteRedirect',
            $donation->id,
            ['give-donation-id' => $donation->id]
        );

        return new RedirectOffsite($redirectUrl);
    }

    /**
     * An example of using a routeMethod for extending the Gateway API to handle a redirect.
     *
     * @since 2.21.0 update to use Donation model
     * @since 2.19.0
     *
     * @param array $queryParams
     *
     * @return RedirectResponse
     * @throws Exception
     * @throws PaymentGatewayException
     */
    protected function returnFromOffsiteRedirect(array $queryParams): RedirectResponse
    {
        $donation = Donation::find($queryParams['give-donation-id']);

        $this->updateDonation($donation);

        return response()->redirectTo(give_get_success_page_uri());
    }

    /**
     * An example of using a secureRouteMethod for extending the Gateway API to handle a redirect.
     *
     * @since 2.21.0 update to use Donation model
     * @since 2.19.0
     *
     * @param array $queryParams
     *
     * @return RedirectResponse
     * @throws Exception
     */
    protected function securelyReturnFromOffsiteRedirect(array $queryParams): RedirectResponse
    {
        $donation = Donation::find($queryParams['give-donation-id']);

        $this->updateDonation($donation);

        return response()->redirectTo(give_get_success_page_uri());
    }

    /**
     * @param Donation $donation
     *
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

    /**
     * @since 2.20.0
     * @inerhitDoc
     * @throws Exception
     */
    public function refundDonation(Donation $donation)
    {
        throw new Exception('Method has not been implemented yet. Please use the legacy method in the meantime.');
    }
}
