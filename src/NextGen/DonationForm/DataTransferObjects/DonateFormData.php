<?php

namespace Give\NextGen\DonationForm\DataTransferObjects;

use Give\PaymentGateways\DataTransferObjects\FormData;
use Give\ValueObjects\Address;
use Give\ValueObjects\CardInfo;
use Give\ValueObjects\DonorInfo;

/**
 * @unreleased
 */
class DonateFormData extends FormData
{

    /**
     * Convert data from request into DTO
     *
     * @unreleased
     *
     * @return self
     */
    public static function fromRequest(array $request): FormData
    {
        $self = new static();

        $self->price = $request['amount'];
        $self->priceId = '';
        $self->amount = $request['amount'];
        $self->date = '';
        $self->purchaseKey = '';
        $self->currency = $request['currency'];
        $self->formTitle = $request['formTitle'];
        $self->formId = (int)$request['formId'];
        $self->paymentGateway = $request['gatewayId'];

        $self->billingAddress = Address::fromArray([
            'line1' => 'line1',
            'line2' => 'line2',
            'city' => 'city',
            'state' => 'state',
            'country' => 'country',
            'postalCode' => 'postalCode',
        ]);

       $self->donorInfo = DonorInfo::fromArray([
            'wpUserId' => $request['userId'],
            'firstName' => $request['firstName'],
            'lastName' => $request['lastName'],
            'email' => $request['email'],
            'honorific' => '',
            'address' =>[
                'line1' => 'line1',
                'line2' => 'line2',
                'city' => 'city',
                'state' => 'state',
                'country' => 'country',
                'postalCode' => 'postalCode',
            ]
        ]);

        $self->cardInfo = CardInfo::fromArray([
            'name' => '',
            'cvc' => '',
            'expMonth' => '',
            'expYear' => '',
            'number' => '',
        ]);

        return $self;
    }
}
