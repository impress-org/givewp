<?php

namespace Give\NextGen\DonationForm\DataTransferObjects;

use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\Support\ValueObjects\Money;
use Give\NextGen\DonationForm\Models\DonationForm;

class DonateControllerData
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
     * @var string|null
     */
    public $honorific;

    /**
     * @unreleased
     */
    public function toDonation($donorId): Donation
    {
        $form = $this->getDonationForm();

        return new Donation([
            'status' => DonationStatus::PENDING(),
            'gatewayId' => $this->gatewayId,
            'amount' => Money::fromDecimal($this->amount, $this->currency),
            'donorId' => $donorId,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'email' => $this->email,
            'formId' => $this->formId,
            'formTitle' => $form->title,
            'company' => $this->company
        ]);
    }

    /**
     * @unreleased
     */
    public function getDonationForm(): DonationForm
    {
        return DonationForm::find($this->formId);
    }

    /**
     * This is a hard-coded way of filtering through our dynamic properties
     * and only returning custom fields.
     *
     * TODO: figure out a less static way of doing this
     *
     * @unreleased
     */
    public function getCustomFields(): array
    {
        $properties = get_object_vars($this);

        return array_filter($properties, static function ($param) {
            return !in_array(
                $param,
                [
                    'amount',
                    'gatewayId',
                    'currency',
                    'firstName',
                    'lastName',
                    'email',
                    'wpUserId',
                    'formId',
                    'formTitle',
                    'company',
                    'honorific',
                ]
            );
        }, ARRAY_FILTER_USE_KEY);
    }
}
