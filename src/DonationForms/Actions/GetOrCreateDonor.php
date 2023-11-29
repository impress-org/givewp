<?php

namespace Give\DonationForms\Actions;

use Exception;
use Give\Donors\Models\Donor;

/**
 * @since 3.1.2
 */
class GetOrCreateDonor
{
    public $donorCreated = false;

    /**
     * @since 3.1.2
     *
     * @throws Exception
     */
    public function __invoke(
        ?int $userId,
        string $donorEmail,
        string $firstName,
        string $lastName,
        ?string $honorific
    ): Donor {
        // first check if donor exists as a user
        $donor = $userId ? Donor::whereUserId($userId) : null;

        // If they exist as a donor & user then make sure they don't already own this email before adding to their additional emails list..
        if ($donor && !$donor->hasEmail($donorEmail) && !Donor::whereEmail($donorEmail)) {
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
                'userId' => $userId ?: null,
                'prefix' => $honorific,
            ]);

            $this->donorCreated = true;
        }

        return $donor;
    }
}
