<?php

namespace Give\PaymentGateways\Gateways\Stripe\StripePaymentElementGateway\DataTransferObjects;

class StripePaymentIntentData
{
    /**
     * @var string
     */
    public $amount;
    /**
     * @var string
     */
    public $currency;
    /**
     * @var string
     */
    public $customer;
    /**
     * @var string
     */
    public $description;
    /**
     * @var array
     */
    public $metadata;
    /**
     * @var array|null
     *
     */
    public $automaticPaymentMethods;
    /**
     * @var string|null
     */
    public $applicationFeeAmount;
    /**
     * @var string|null
     */
    public $receiptEmail;

    /**
     * @since 3.20.0 removed statement_descriptor. As of 01/02/2024, Stripe no longer supports the `statement_descriptor` parameter on the PaymentIntent API for PaymentIntents in which one of the supported `payment_method_types` is `card`.
     * @since 3.0.0
     *
     * @param  array{amount: string, currency: string, customer: string, description: string, metadata: array, automatic_payment_methods: array, application_fee_amount: string, receipt_email: string }  $array
     */
    public static function fromArray(array $array): self
    {
        $self = new self();
        $self->amount = $array['amount'];
        $self->currency = $array['currency'];
        $self->customer = $array['customer'];
        $self->description = $array['description'];
        $self->metadata = $array['metadata'];
        $self->automaticPaymentMethods = !empty($array['automatic_payment_methods']) ? $array['automatic_payment_methods'] : null;
        $self->applicationFeeAmount = !empty($array['application_fee_amount']) ? $array['application_fee_amount'] : null;
        $self->receiptEmail = !empty($array['receipt_email']) ? $array['receipt_email'] : null;

        return $self;
    }

    /**
     * @since 3.0.0
     */
    public function toParams()
    {
        $args = [
            'amount' => $this->amount,
            'currency' => $this->currency,
            'customer' => $this->customer,
            'description' => $this->description,
            'metadata' => $this->metadata
        ];

        if ($this->automaticPaymentMethods){
            $args['automatic_payment_methods'] = $this->automaticPaymentMethods;
        }

        if ($this->applicationFeeAmount){
            $args['application_fee_amount'] = $this->applicationFeeAmount;
        }

        if ($this->receiptEmail){
            $args['receipt_email'] = $this->receiptEmail;
        }

        return apply_filters(
            'givewp_stripe_create_intent_args',
            $args
        );
    }

    /**
     * @since 3.0.0
     */
    public function toOptions(string $stripeConnectAccountId): array
    {
        return ['stripe_account' => $stripeConnectAccountId];
    }
}
