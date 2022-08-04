<?php

namespace Give\NextGen\DonationForm\DataTransferObjects;

use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\Support\ValueObjects\Money;

/**
 * @unreleased
 */
class DonateFormData
{
    /**
     * @var float
     */
    public $amount;
    /**
     * @var string
     */
    public $gatewayId;
    /**
     * @var string
     */
    public $currency;
    /**
     * @var string
     */
    public $firstName;
    /**
     * @var string
     */
    public $lastName;
    /**
     * @var string
     */
    public $email;
    /**
     * @var int
     */
    public $wpUserId;
    /**
     * @var int
     */
    public $formId;
    /**
     * @var string
     */
    public $formTitle;
    /**
     * @var string|null
     */
    public $company;

    /**
     * Convert data from request into DTO
     *
     * @unreleased
     *
     * @param  array  $request
     * @return DonateFormData
     */
    public static function fromRequest(array $request): DonateFormData
    {
        $self = new static();

        $self->gatewayId = $request['gatewayId'];
        $self->amount = $request['amount'];
        $self->currency = $request['currency'];
        $self->firstName = $request['firstName'];
        $self->lastName = $request['lastName'];
        $self->email = $request['email'];
        $self->wpUserId = get_current_user_id();
        $self->formId = (int)$request['formId'];
        $self->formTitle = get_the_title($request['formId']);
        $self->company = !empty($request['company']) ? $request['company'] : null;

        return $self;
    }

    /**
     * @unreleased
     */
    public function toDonation($donorId): Donation
    {
        return new Donation([
            'status' => DonationStatus::PENDING(),
            'gatewayId' => $this->gatewayId,
            'amount' => Money::fromDecimal($this->amount, $this->currency),
            'donorId' => $donorId,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'email' => $this->email,
            'formId' => $this->formId,
            'formTitle' => $this->formTitle,
            'company' => $this->company
        ]);
    }
}
