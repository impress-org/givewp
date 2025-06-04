<?php

namespace Give\Donors\ViewModels;

use Give\API\REST\V3\Routes\Donors\ValueObjects\DonorAnonymousMode;
use Give\Donors\Models\Donor;

/**
 * @unreleased
 */
class DonorViewModel
{
    private Donor $donor;
    private DonorAnonymousMode $anonymousMode;
    private bool $includeSensitiveData = false;

    /**
     * @unreleased
     */
    public function __construct(Donor $donor)
    {
        $this->donor = $donor;
    }

    /**
     * @unreleased
     */
    public function includeSensitiveData(bool $includeSensitiveData = true): DonorViewModel
    {
        $this->includeSensitiveData = $includeSensitiveData;

        return $this;
    }


    /**
     * @unreleased
     */
    public function anonymousMode(DonorAnonymousMode $mode): DonorViewModel
    {
        $this->anonymousMode = $mode;

        return $this;
    }

    /**
     * @unreleased
     */
    public function exports(): array
    {
        $data = array_merge(
            $this->donor->toArray(),
            [
                'avatarUrl' => $this->getAvatarUrl(),
                'wpUserPermalink' => $this->donor->userId ? get_edit_user_link($this->donor->userId) : null,
            ],
        );

        if ( ! $this->includeSensitiveData) {
            $sensitiveData = [
                'userId',
                'email',
                'phone',
                'additionalEmails',
                'avatarUrl',
                'company',
                'wpUserPermalink'
            ];

            foreach ($sensitiveData as $propertyName) {
                if (isset($data[$propertyName])) {
                    $data[$propertyName] = '';
                }
            }
        }


        if ($this->anonymousMode->isRedacted() && $this->donor->isAnonymous()) {
            $sensitiveData = [
                'id',
                'name',
                'firstName',
                'lastName',
                'prefix',
                'avatarUrl',
                'company'
            ];

            foreach ($sensitiveData as $propertyName) {
                switch ($propertyName) {
                    case 'id':
                        $data[$propertyName] = 0;
                        break;
                    case 'avatarUrl':
                        $data[$propertyName] = '';
                        break;
                    default:
                        $data[$propertyName] = __('anonymous', 'give');
                        break;
                }
            }
        }

        return $data;
    }

    /**
     * Get avatar URL from avatar ID with fallback to Gravatar
     *
     * @unreleased
     */
    private function getAvatarUrl(): ?string
    {
        $avatarId = $this->donor->avatarId;

        if ($avatarId) {
            return wp_get_attachment_url($avatarId);
        } else {
            return give_validate_gravatar($this->donor->email) ? get_avatar_url($this->donor->email, ['size' => 80]) : null;
        }
    }
}
