<?php

namespace Give\NextGen\DonationForm\Controllers;

use Exception;
use Give\Donors\Models\Donor;
use Give\Framework\PaymentGateways\PaymentGateway;
use Give\NextGen\DonationForm\DataTransferObjects\DonateFormData;

/**
 * @unreleased
 */
class DonateController
{

    /**
     * First we create a donation, then move on to the gateway processing
     *
     * @unreleased
     *
     * @param  DonateFormData  $formData
     * @param  PaymentGateway  $registeredGateway
     *
     * @return void
     * @throws Exception
     */
    public function donate(DonateFormData $formData, PaymentGateway $registeredGateway)
    {
        $donor = $this->getOrCreateDonor(
            $formData->wpUserId,
            $formData->email,
            $formData->firstName,
            $formData->lastName
        );

        $donation = $formData->toDonation($donor->id);
        $donation->save();

        $this->setSession($donation->id);

        $registeredGateway->handleCreatePayment($donation);
    }

    /**
     * @unreleased
     *
     * @param  int|null  $userId
     * @param  string  $donorEmail
     * @param  string  $firstName
     * @param  string  $lastName
     *
     * @return Donor
     * @throws Exception
     */
    private function getOrCreateDonor(
        int $userId,
        string $donorEmail,
        string $firstName,
        string $lastName
    ): Donor {
        // first check if donor exists as a user
        $donor = Donor::whereUserId($userId);

        // If they exist as a donor & user then make sure they don't already own this email before adding to their additional emails list..
        if ($donor && !$donor->hasEmail($donorEmail)) {
            $donor->additionalEmails = array_merge($donor->additionalEmails ?? [], [$donorEmail]);
            $donor->save();
        }

        // if donor is not a user than check for any donor matching this email
        if (!$donor) {
            $donor = Donor::whereEmail($donorEmail);
        }

        // if no donor exists then create a new one using their personal information from the form.
        if (!$donor) {
            $donor = Donor::create([
                'name' => trim("$firstName $lastName"),
                'firstName' => $firstName,
                'lastName' => $lastName,
                'email' => $donorEmail,
                'userId' => $userId ?: null
            ]);
        }

        return $donor;
    }

    /**
     * Set donation id to purchase session for use in the donation receipt.
     *
     * @unreleased
     *
     * @param $donationId
     *
     * @return void
     */
    private function setSession($donationId)
    {
        $purchaseSession = (array)give()->session->get('give_purchase');

        if ($purchaseSession && array_key_exists('purchase_key', $purchaseSession)) {
            $purchaseSession['donation_id'] = $donationId;
            give()->session->set('give_purchase', $purchaseSession);
        }
    }
}
