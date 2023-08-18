<?php

namespace Give\DonationForms\DataTransferObjects;

use Give\Donations\Models\Donation;
use Give\Donors\Models\Donor;

/**
 * This DTO extracts the complexity of supplying an array for use in give_set_purchase_session()
 *
 * @since 3.0.0
 */
class LegacyPurchaseFormData
{
    /**
     * @var Donation
     */
    public $donation;
    /**
     * @var Donor
     */
    public $donor;

    /**
     * @since 3.0.0
     *
     * @param  array{donation: Donation, donor: Donation}  $array
     * @return LegacyPurchaseFormData
     */
    public static function fromArray(array $array): self
    {
        $self = new self();

        $self->donation = $array['donation'];
        $self->donor = $array['donor'];

        return $self;
    }

    /**
     * Returns shape needed for give_set_purchase_session()
     *
     * @since 3.0.0
     */
    public function toPurchaseData(): array
    {
        $address = [
            'line1' => $this->donation->billingAddress->address1,
            'line2' => $this->donation->billingAddress->address2,
            'city' => $this->donation->billingAddress->city,
            'state' => $this->donation->billingAddress->state,
            'country' => $this->donation->billingAddress->country,
            'zip' => $this->donation->billingAddress->zip
        ];

        return [
            'donation_id' => $this->donation->id,
            'price' => $this->donation->amount->formatToDecimal(),
            'purchase_key' => $this->donation->purchaseKey,
            'user_email' => $this->donor->email,
            'date' => $this->donation->createdAt->format('Y-m-d H:i:s'),
            'user_info' => [
                'id' => $this->donor->id,
                'firstName' => $this->donor->firstName,
                'lastName' => $this->donor->lastName,
                'title' => $this->donor->prefix,
                'email' => $this->donor->email,
                'address' => $address
            ],
            'post_data' => [
                'amount' => $this->donation->amount->formatToDecimal(),
                'first' => $this->donation->firstName,
                'last' => $this->donation->lastName,
                'email' => $this->donation->email,
                'userId' => get_current_user_id(),
                'gateway' => $this->donation->gatewayId,
            ],
            'gateway' => $this->donation->gatewayId,
            'card_info' => [
                'address' => $address
            ],
        ];
    }

}
