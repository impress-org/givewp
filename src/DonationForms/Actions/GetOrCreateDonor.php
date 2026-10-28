<?php

namespace Give\DonationForms\Actions;

use Exception;
use Give\Donors\Models\Donor;

/**
 * @since 3.2.0
 */
class GetOrCreateDonor
{
    public $donorCreated = false;

    /**
     * @since TBD Do not append submitted donation email to existing donor additional emails.
     * @since 3.9.0 Add support to "phone" property
     * @since 3.2.0
     *
     * @throws Exception
     */
    public function __invoke(
        ?int $userId,
        string $donorEmail,
        string $firstName,
        string $lastName,
        ?string $honorific,
        ?string $donorPhone
    ): Donor {
        // first check if donor exists as a user
        $donor = $userId ? Donor::whereUserId($userId) : null;

        // if donor is not a user than check for any donor matching this email
        if (!$donor) {
            $donor = Donor::whereEmail($donorEmail);
        }

        // if they exist as a donor & user but don't have a phone number then add it to their profile.
        if ($donor && empty($donor->phone)) {
            $donor->phone = $donorPhone;
            $donor->save();
        }

        // if no donor exists then create a new one using their personal information from the form.
        if (!$donor) {
            $donor = Donor::create([
                'name' => trim("$firstName $lastName"),
                'firstName' => $firstName,
                'lastName' => $lastName,
                'email' => $donorEmail,
                'phone' => $donorPhone,
                'userId' => $userId ?: null,
                'prefix' => $honorific,
            ]);

            $this->donorCreated = true;
        }

        return $donor;
    }
}
