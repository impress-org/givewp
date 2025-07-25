<?php

namespace Give\Subscriptions\ViewModels;

use Give\API\REST\V3\Routes\Donors\ValueObjects\DonorAnonymousMode;
use Give\Subscriptions\Models\Subscription;

/**
 * @unreleased
 */
class SubscriptionViewModel
{
    private Subscription $subscription;

    private DonorAnonymousMode $anonymousMode;

    private bool $includeSensitiveData = false;

    /**
     * @unreleased
     */
    public function __construct(Subscription $subscription)
    {
        $this->subscription = $subscription;
    }

    /**
     * @unreleased
     */
    public function includeSensitiveData(bool $includeSensitiveData = true): SubscriptionViewModel
    {
        $this->includeSensitiveData = $includeSensitiveData;

        return $this;
    }    

    /**
    * @unreleased
    */
    public function anonymousMode(DonorAnonymousMode $mode): SubscriptionViewModel
    {
        $this->anonymousMode = $mode;

        return $this;
    }

    /**
     * @unreleased
     */
    public function exports(): array
    {
        $data = array_merge(
            $this->subscription->toArray(),
            [
                'gateway' => $this->getGatewayDetails(),
            ]
        );

        if ( ! $this->includeSensitiveData) {
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
        
        if (isset($this->anonymousMode ) && $this->anonymousMode->isRedacted() && $this->subscription->donor->isAnonymous()) {
            $anonymousDataRedacted = [
                'donorId',                
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
     * @unreleased
     */
    private function getGatewayDetails(): array
    {
        return array_merge(
            $this->subscription->gateway()->toArray(),
            [
                'subscriptionUrl' => $this->subscription->gateway()->gatewayDashboardSubscriptionUrl($this->subscription),
            ]
        );
    }
}
