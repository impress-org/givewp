<?php

namespace Give\PaymentGateways\Gateways\Stripe\Webhooks;

use Give\Donations\Models\Donation;
use Give\Donations\Repositories\DonationRepository;
use Give\Framework\Exceptions\Primitives\Exception;
use Give\PaymentGateways\Gateways\Stripe\Traits\CanSetupStripeApp;
use Give\PaymentGateways\Gateways\Stripe\Webhooks\Contracts\EventListener;
use Stripe\Event;

/**
 * @since 2.21.0
 */
abstract class StripeEventListener implements EventListener
{
    use CanSetupStripeApp;

    /**
     * 4.14.1 Pass connected account ID to Event::retrieve for Stripe Connect support
     * @since 2.21.0
     * @throws Exception
     */
    public function __invoke(Event $event)
    {
        if ($formId = $this->getFormId($event)) {
            $this->setupStripeApp($formId);
            $this->logEventReceiveTime();

            $this->processEvent($this->getEventFromStripe($event->id, $formId));
        }
    }

    /**
     * Retrieves the full event from Stripe.
     *
     * For Stripe Connect accounts, we need to pass the connected account ID to retrieve the event from the correct account, not the platform account.
     *
     * 4.14.1 Added $formId parameter and stripe_account option for Stripe Connect support
     * @since 2.21.0
     *
     * @param string $eventId
     * @param int|null $formId Form ID to determine the connected account
     *
     * @return Event
     * @throws Exception
     */
    protected function getEventFromStripe($eventId, $formId = null)
    {
        try {
            $options = $this->getStripeConnectOptions($formId);

            return Event::retrieve($eventId, $options);
        } catch (\Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Get Stripe Connect options for API calls.
     *
     * Returns an array with the stripe_account option if a connected account is configured for the given form. This is required for Stripe Connect to make API calls to the correct connected account instead of the platform account.
     *
     * 4.14.1
     *
     * @param int|null $formId Form ID to determine the connected account
     *
     * @return array Options array for Stripe API calls
     */
    protected function getStripeConnectOptions($formId = null): array
    {
        if ($formId) {
            $connectedAccountId = give_stripe_get_connected_account_id($formId);
            if (!empty($connectedAccountId)) {
                return ['stripe_account' => $connectedAccountId];
            }
        }

        return [];
    }

    /**
     * @since 2.21.0
     * @return void
     */
    private function logEventReceiveTime()
    {
        // Update time of webhook received whenever the event is retrieved.
        give_update_option('give_stripe_last_webhook_received_timestamp', time());
    }

    /**
     * @since 2.21.0
     *
     * @return int|null
     */
    protected function getFormId(Event $event)
    {
        if ($donation = $this->getDonation($event)) {
            return $donation->formId;
        }

        return null;
    }

    /**
     * @since 2.21.0
     *
     * @return Donation
     */
    protected function getDonation(Event $event)
    {
        return give(DonationRepository::class)->getByGatewayTransactionId($event->data->object->id);
    }
}
