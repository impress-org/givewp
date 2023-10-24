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
     * @var string
     */
    public $statementDescriptor;
    /**
     * @var string|null
     */
    public $receiptEmail;

    /**
     * @since 3.0.0
     *
     * @param  array{amount: string, currency: string, customer: string, description: string, metadata: array, automatic_payment_methods: array, application_fee_amount: string, statement_descriptor: string, receipt_email: string }  $array
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
        $self->statementDescriptor = $array['statement_descriptor'];
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
            'metadata' => $this->metadata,
            'statement_descriptor' => $this->statementDescriptor,
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