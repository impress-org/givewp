<?php

namespace Give\DonorDashboards;

use Exception;
use Give\DonorDashboards\Factories\DonorFactory;
use Give\DonorDashboards\Helpers as DonorDashboardHelpers;
use Give\DonorDashboards\Pipeline\DonorProfilePipeline;
use Give\DonorDashboards\Pipeline\Stages\UpdateDonorAddresses;
use Give\DonorDashboards\Pipeline\Stages\UpdateDonorAnonymousGiving;
use Give\DonorDashboards\Pipeline\Stages\UpdateDonorAvatar;
use Give\DonorDashboards\Pipeline\Stages\UpdateDonorCompany;
use Give\Donors\Models\Donor;

/**
 * @since 2.10.0
 */
class Profile
{

    protected $donor;
    protected $id;

    public function __construct()
    {
        $donorId = DonorDashboardHelpers::getCurrentDonorId();
        if ($donorId) {
            $donorFactory = new DonorFactory;
            $this->donor = $donorFactory->make($donorId);
        }
    }

    /**
     * Handles updating relevant profile fields in donor database and meta database
     *
     * @since 2.27.3 Use Donor model to update data used by webhooks addon to prevent multiple events creation
     * @since      2.10.0
     *
     * @param object $data Object representing profile data to update
     *
     * @return array
     *
     * @throws Exception
     */
    public function update($data)
    {
        $donor = Donor::find($this->donor->id);

        $donor->email = $data['primaryEmail'];
        $donor->additionalEmails = $data['additionalEmails'] ?: [];

        if ( ! empty($data['firstName']) && ! empty($data['lastName'])) {
            $firstName = $data['firstName'];
            $lastName = $data['lastName'];
            $donor->name = "{$firstName} {$lastName}";
            $donor->firstName = $firstName;
            $donor->lastName = $lastName;
        }

        $donor->save();

        $pipeline = (new DonorProfilePipeline)
            ->pipe(new UpdateDonorCompany)
            ->pipe(new UpdateDonorAvatar)
            ->pipe(new UpdateDonorAddresses)
            ->pipe(new UpdateDonorAnonymousGiving);

        $pipeline->process(
            [
                'data' => $data,
                'donor' => $this->donor,
            ]
        );

        // Return updated donor profile data
        return $this->getProfileData();
    }

    /**
     * Return array of donor profile data
     *
     * @since 2.10.0
     *
     * @return array
     */
    public function getProfileData()
    {
        if ( ! $this->donor) {
            return null;
        }

        $titlePrefix = Give()->donor_meta->get_meta($this->donor->id, '_give_donor_title_prefix', true);

        return [
            'name' => give_get_donor_name_with_title_prefixes($titlePrefix, $this->donor->name),
            'firstName' => $this->donor->get_first_name(),
            'lastName' => $this->donor->get_last_name(),
            'emails' => $this->donor->emails,
            'sinceLastDonation' => ! empty($this->donor->get_last_donation_date()) ? human_time_diff(
                strtotime($this->donor->get_last_donation_date())
            ) : '',
            'avatarUrl' => $this->getAvatarUrl(),
            'avatarId' => $this->getAvatarId(),
            'sinceCreated' => human_time_diff(strtotime($this->donor->date_created)),
            'company' => $this->donor->get_company_name(),
            'initials' => $this->donor->get_donor_initals(),
            'titlePrefix' => $this->getTitlePrefix(),
            'addresses' => $this->donor->address,
            'isAnonymous' => $this->donor->get_meta('_give_anonymous_donor', true) !== '0' ? '1' : '0',
        ];
    }

    /**
     * Returns profile model's donor id
     *
     * @since 2.10.0
     * @return int
     *
     */
    public function getId()
    {
        if ( ! $this->donor) {
            return null;
        }

        return $this->donor->id;
    }

    /**
     * Returns donor's title prefix
     *   *
     * @since 2.10.0
     * @return string
     *
     */
    public function getTitlePrefix()
    {
        return Give()->donor_meta->get_meta($this->donor->id, '_give_donor_title_prefix', true);
    }

    /**
     * Returns profile's avatar URL
     *   *
     * @since 2.10.0
     * @return string
     *
     */
    public function getAvatarUrl()
    {
        $avatarId = $this->getAvatarId();
        if ($avatarId) {
            return wp_get_attachment_url($avatarId);
        } else {
            return give_validate_gravatar($this->donor->email) ? get_avatar_url($this->donor->email, ['size' => 140]
            ) : null;
        }
    }

    /**
     * Returns profile's avatar media ID
     *   *
     * @since 2.10.0
     * @return int
     *
     */
    public function getAvatarId()
    {
        return $this->donor->get_meta('_give_donor_avatar_id');
    }

    /**
     * Returns profile's stored country, or global default if none is set
     *   *
     * @since 2.10.0
     * @return string
     *
     */
    public function getCountry()
    {
        if ( ! $this->donor) {
            return give_get_country();
        }

        $address = $this->donor->get_donor_address();
        if ($address) {
            return $address['country'];
        } else {
            return give_get_country();
        }
    }
}
