<?php

namespace Give\PaymentGateways\Gateways\TestOffsiteGateway;

use Give\Donations\Models\Donation;
use Give\Donations\Models\DonationNote;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\Http\Response\Types\RedirectResponse;
use Give\Framework\PaymentGateways\Commands\PaymentRefunded;
use Give\Framework\PaymentGateways\Commands\RedirectOffsite;
use Give\Framework\PaymentGateways\PaymentGateway;
use Give\Framework\Support\Facades\Scripts\ScriptAsset;
use Give\Helpers\Form\Utils as FormUtils;
use Give\Helpers\Language;
use Give\PaymentGateways\Gateways\TestGateway\Views\LegacyFormFieldMarkup;

/**
 * Class TestGatewayOffsite
 * @since 2.18.0
 */
class TestOffsiteGateway extends PaymentGateway
{
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
        return 'test-offsite-gateway';
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
        return __('Test Gateway (Offsite)', 'give');
    }

    /**
     * @since 3.0.0
     */
    public function formSettings(int $formId): array
    {
        return [
            'message' => __(
                'There are no fields for this gateway and you will not be charged. This payment option is only for you to test the donation experience.',
                'give'
            )
        ];
    }

    /**
     * @inheritDoc
     */
    public function getPaymentMethodLabel(): string
    {
        return __('Test Gateway (Offsite)', 'give');
    }

    /**
     * @since 3.1.0 set translations for script
     * @since 2.30.0
     */
    public function enqueueScript(int $formId)
    {
        $scriptAsset = ScriptAsset::get(GIVE_PLUGIN_DIR . 'build/testOffsiteGateway.asset.php');
        $handle = $this::id();

        wp_enqueue_script(
            $this::id(),
            GIVE_PLUGIN_URL . 'build/testOffsiteGateway.js',
            $scriptAsset['dependencies'],
            $scriptAsset['version'],
            true
        );

        Language::setScriptTranslations($handle);
    }

    /**
     * @since 2.18.0
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
    public function createPayment(Donation $donation, $gatewayData)
    {
        $redirectUrl = $this->generateSecureGatewayRouteUrl(
            'securelyReturnFromOffsiteRedirect',
            $donation->id,
            [
                'givewp-donation-id' => $donation->id,
                'givewp-return-url' => $gatewayData['successUrl']
            ]
        );

        return new RedirectOffsite($redirectUrl);
    }

    /**
     * @since 2.29.0 Return PaymentRefunded instead of a bool value
     * @since      2.20.0
     * @inerhitDoc
     */
    public function refundDonation(Donation $donation): PaymentRefunded
    {
        return new PaymentRefunded();
    }

    /**
     * An example of using a secureRouteMethod for extending the Gateway API to handle a redirect.
     *
     * @since 3.0.0
     *
     * @throws Exception
     */
    protected function securelyReturnFromOffsiteRedirect(array $queryParams): RedirectResponse
    {
        /** @var Donation $donation */
        $donation = Donation::find($queryParams['givewp-donation-id']);
        $this->updateDonation($donation);

        return new RedirectResponse($queryParams['givewp-return-url']);
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
}
