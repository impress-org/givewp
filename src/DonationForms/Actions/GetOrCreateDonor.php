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
     * @since TBD Link a donor matched by email to the logged-in user when it is the user's account email
     * @since 4.19.1.1 Do not append submitted donation email to existing donor additional emails.
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

            if ($donor && $userId) {
                $this->linkDonorToUser($donor, $userId);
            }
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

    /**
     * Connects an unlinked donor to the logged-in user, only when the donor's email is the user's account email.
     *
     * The email entered on the form is not enough: an account created with someone else's email
     * must not gain access to that donor's history.
     *
     * @since TBD
     *
     * @throws Exception
     */
    private function linkDonorToUser(Donor $donor, int $userId): void
    {
        if ($donor->userId) {
            return;
        }

        $user = get_userdata($userId);

        if (!$user || strcasecmp($user->user_email, $donor->email) !== 0) {
            return;
        }

        $donor->userId = $userId;
        $donor->save();
    }
}
