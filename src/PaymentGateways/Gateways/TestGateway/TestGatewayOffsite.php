<?php

namespace Give\PaymentGateways\Gateways\TestGateway;

use Give\Donations\Models\Donation;
use Give\Donations\Models\DonationNote;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\Http\Response\Types\RedirectResponse;
use Give\Framework\PaymentGateways\Commands\GatewayCommand;
use Give\Framework\PaymentGateways\Commands\PaymentRefunded;
use Give\Framework\PaymentGateways\Commands\RedirectOffsite;
use Give\Framework\PaymentGateways\PaymentGateway;
use Give\Helpers\Form\Utils as FormUtils;
use Give\PaymentGateways\Gateways\TestGateway\Views\LegacyFormFieldMarkup;
use Give\Subscriptions\Models\Subscription;
use Give\Subscriptions\ValueObjects\SubscriptionStatus;

/**
 * Class TestGatewayOffsite
 * @since 2.18.0
 */
class TestGatewayOffsite extends PaymentGateway
{
    /**
     * @inheritDoc
     */
    public $secureRouteMethods = [
        'securelyReturnFromOffsiteRedirect'
    ];

    /**
     * @since 2.30.0
     */
    public function enqueueScript(int $formId)
    {
        // This is temporary action to enqueue gateway scripts in the GiveWP 3.0 feature plugin.
        // Eventually, these scripts will be moved to the GiveWP core plugin.
        // TODO: enqueue scripts for 3.0 when feature plugin is merged into GiveWP
        do_action('givewp_donation_form_enqueue_test_gateway_offsite_scripts');
    }

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
        return __('Test Gateway (Offsite)', 'give');
    }

    /**
     * @inheritDoc
     */
    public function getPaymentMethodLabel(): string
    {
        return __('Test Gateway (Offsite)', 'give');
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
     * @inheritDoc
     */
    public function createSubscription(
        Donation $donation,
        Subscription $subscription,
        $gatewayData
    ): GatewayCommand {
        $redirectUrl = $this->generateSecureGatewayRouteUrl(
            'securelyReturnFromOffsiteRedirect',
            $donation->id,
            [
                'givewp-donation-id' => $donation->id,
                'give-subscription-id' => $subscription->id,
                'givewp-return-url' => $gatewayData['successUrl']
            ]
        );

        return new RedirectOffsite($redirectUrl);
    }

    /**
     * An example of using a secureRouteMethod for extending the Gateway API to handle a redirect.
     *
     * @since 2.30.0 update with new gatewayData params
     * @since 2.21.0 update to use Donation model
     * @since 2.19.0
     *
     * @param  array  $queryParams
     *
     * @return RedirectResponse
     * @throws Exception
     * @throws \Exception
     */
    protected function securelyReturnFromOffsiteRedirect(array $queryParams): RedirectResponse
    {
        /** @var Donation $donation */
        $donation = Donation::find($queryParams['givewp-donation-id']);
        $this->updateDonation($donation);

        if ($donation->type->isSubscription()) {
            /** @var Subscription $subscription */
            $subscription = Subscription::find($queryParams['givewp-subscription-id']);
            $this->updateSubscription($subscription);
        }

        return new RedirectResponse($queryParams['givewp-return-url']);
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
     * @since 2.23.0
     *
     * @return void
     * @throws \Exception
     */
    private function updateSubscription(Subscription $subscription)
    {
        $subscription->status = SubscriptionStatus::ACTIVE();
        $subscription->transactionId = "test-gateway-transaction-id";
        $subscription->save();
    }
}
