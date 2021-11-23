<?php

namespace Give\DonorDashboards\Pipeline\Stages;

/**
 * @since 2.10.0
 */
class UpdateDonorEmails implements Stage
{

    protected $data;
    protected $donor;

    public function __invoke($payload)
    {
        $this->data = $payload['data'];
        $this->donor = $payload['donor'];

        $this->updateEmailsInMetaDB();
        $this->updateEmailsInDonorDB();

        return $payload;
    }

    /**
     * Updates additional emails stored in meta database
     *
     * @since 2.10.0
     * @return void
     *
     */
    protected function updateEmailsInMetaDB()
    {
        $additionalEmails = $this->data['additionalEmails'] ? $this->data['additionalEmails'] : [];

        /**
         * Remove additional emails that exist in the donor meta table,
         * but do not appear in the new array of additional emails
         */

        $storedAdditionalEmails = $this->donor->get_meta('additional_email', false);
        $diffEmails = array_diff($storedAdditionalEmails, $additionalEmails);

        foreach ($diffEmails as $diffEmail) {
            $this->donor->delete_meta('additional_email', $diffEmail);
        }

        /**
         * Add any new additional emails
         */

        foreach ($additionalEmails as $email) {
            if ( ! in_array($email, $storedAdditionalEmails)) {
                $this->donor->add_meta('additional_email', $email);
            }
        }
    }

    protected function updateEmailsInDonorDB()
    {
        $updateArgs = [];
        if ( ! empty($this->data['primaryEmail'])) {
            $updateArgs['email'] = $this->data['primaryEmail'];
        }

        $this->donor->update($updateArgs);
    }
}
