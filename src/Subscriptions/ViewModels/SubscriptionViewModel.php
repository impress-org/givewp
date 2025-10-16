<?php

namespace Give\Subscriptions\ViewModels;

use Give\API\REST\V3\Routes\Donors\ValueObjects\DonorAnonymousMode;
use Give\Framework\PaymentGateways\Contracts\Subscription\SubscriptionTransactionsSynchronizable;
use Give\Framework\PaymentGateways\PaymentGatewayRegister;
use Give\Subscriptions\Models\Subscription;

/**
 * @since 4.8.0
 */
class SubscriptionViewModel
{
    private Subscription $subscription;

    private DonorAnonymousMode $anonymousMode;

    private bool $includeSensitiveData = false;

    /**
     * @since 4.8.0
     */
    public function __construct(Subscription $subscription)
    {
        $this->subscription = $subscription;
    }

    /**
     * @since 4.8.0
     */
    public function includeSensitiveData(bool $includeSensitiveData = true): SubscriptionViewModel
    {
        $this->includeSensitiveData = $includeSensitiveData;

        return $this;
    }

    /**
    * @since 4.8.0
    */
    public function anonymousMode(DonorAnonymousMode $mode): SubscriptionViewModel
    {
        $this->anonymousMode = $mode;

        return $this;
    }

    /**
     * @since 4.10.0 added campaignId
     * @since 4.8.0
     */
    public function exports(): array
    {
        $donor = $this->subscription->donor;

        $data = array_merge(
            $this->subscription->toArray(),
            [
                'firstName' => $donor ? $donor->firstName : '',
                'lastName' => $donor ? $donor->lastName : '',
                'gateway' => $this->getGatewayDetails(),
                'projectedAnnualRevenue' => $this->subscription->projectedAnnualRevenue(),
                'campaignId' => $this->subscription->campaign ? $this->subscription->campaign->id : null,
            ]
        );

        if (!$this->includeSensitiveData) {
            $sensitiveDataExcluded = [
                'transactionId',
                'gatewaySubscriptionId',
            ];

            foreach ($sensitiveDataExcluded as $propertyName) {
                switch ($propertyName) {
                    default:
                        $data[$propertyName] = '';
                        break;
                }
            }
        }

        if (isset($this->anonymousMode) && $this->anonymousMode->isRedacted() && $this->subscription->donor->isAnonymous()) {
            $anonymousDataRedacted = [
                'donorId',
                'firstName',
                'lastName',
            ];

            foreach ($anonymousDataRedacted as $propertyName) {
                switch ($propertyName) {
                    case 'donorId':
                        $data[$propertyName] = 0;
                        break;
                    default:
                        $data[$propertyName] = __('anonymous', 'give');
                        break;
                }
            }
        }

        return $data;
    }

    /**
     * @since 4.10.0 Return null if subscription URL is not available
     * @since 4.8.0
     */
    private function getGatewayDetails(): ?array
    {
        if (empty($this->subscription->gatewayId) || !give(PaymentGatewayRegister::class)->hasPaymentGateway($this->subscription->gatewayId)) {
            return null;
        }

        $subscriptionUrl = $this->subscription->gateway()->gatewayDashboardSubscriptionUrl($this->subscription);

        return array_merge(
            $this->subscription->gateway()->toArray(),
            [
                'subscriptionUrl' => $subscriptionUrl ?: null,
                'canSync' => $this->subscription->gateway()->subscriptionModule instanceof SubscriptionTransactionsSynchronizable
            ]
        );
    }
}
