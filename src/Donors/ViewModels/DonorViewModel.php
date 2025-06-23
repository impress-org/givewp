<?php

namespace Give\Donors\ViewModels;

use Give\API\REST\V3\Routes\Donors\ValueObjects\DonorAnonymousMode;
use Give\DonationForms\Models\DonationForm;
use Give\DonationForms\Repositories\DonationFormRepository;
use Give\Donations\Models\Donation;
use Give\Donors\Models\Donor;
use Give\Framework\FieldsAPI\Field;
use Give\Framework\FieldsAPI\Types;

/**
 * @since 4.4.0
 */
class DonorViewModel
{
    private Donor $donor;
    private DonorAnonymousMode $anonymousMode;
    private bool $includeSensitiveData = false;

    /**
     * @since 4.4.0
     */
    public function __construct(Donor $donor)
    {
        $this->donor = $donor;
    }

    /**
     * @since 4.4.0
     */
    public function includeSensitiveData(bool $includeSensitiveData = true): DonorViewModel
    {
        $this->includeSensitiveData = $includeSensitiveData;

        return $this;
    }


    /**
     * @since 4.4.0
     */
    public function anonymousMode(DonorAnonymousMode $mode): DonorViewModel
    {
        $this->anonymousMode = $mode;

        return $this;
    }

    /**
     * @since 4.4.0
     */
    public function exports(): array
    {
        $data = array_merge(
            $this->donor->toArray(),
            [
                'addresses' => array_map(function($address) { return $address->toArray(); }, $this->donor->addresses),
                'avatarUrl' => $this->getAvatarUrl(),
                'wpUserPermalink' => $this->donor->userId ? get_edit_user_link($this->donor->userId) : null,
                'customFields' => $this->getCustomFields(),
            ],
        );

        if ( ! $this->includeSensitiveData) {
            $sensitiveDataExcluded = [
                'userId',
                'email',
                'phone',
                'additionalEmails',
                'lastName',
                'avatarUrl',
                'company',
                'addresses',
                'wpUserPermalink',
                'customFields'
            ];

            foreach ($sensitiveDataExcluded as $propertyName) {
    switch ($propertyName) {
                    case 'additionalEmails':
                    case 'customFields':
                        $data[$propertyName] = [];
                        break;
                    default:
                        $data[$propertyName] = '';
                        break;
                }
            }
        }


        if (isset($this->anonymousMode) && $this->anonymousMode->isRedacted() && $this->donor->isAnonymous()) {
            $anonymousDataRedacted = [
                'id',
                'name',
                'firstName',
                'lastName',
                'prefix',
                'avatarUrl',
                'company',
                'email',
                'phone',
                'additionalEmails',
                'wpUserPermalink',
                'customFields'
            ];

            foreach ($anonymousDataRedacted as $propertyName) {
                switch ($propertyName) {
                    case 'id':
                        $data[$propertyName] = 0;
                        break;
                    case 'wpUserPermalink':
                    case 'avatarUrl':
                        $data[$propertyName] = '';
                        break;
                    case 'additionalEmails':
                    case 'customFields':
                        $data[$propertyName] = [];
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
     * @since 4.4.0
     */
    private function getAvatarUrl(): ?string
    {
        $avatarId = $this->donor->avatarId;

        if ($avatarId) {
            return wp_get_attachment_image_url($avatarId, ['width' => '80', 'height' => '80']);
        } else {
            return give_validate_gravatar($this->donor->email) ? get_avatar_url($this->donor->email, ['size' => 80]) : null;
        }
    }

    /**
     * Get custom fields for the donor
     *
     * @since 4.4.0
     */
    private function getCustomFields(): array
    {
        $forms = $this->getUniqueDonationFormsForDonor();

        if (empty($forms)) {
            return [];
        }

        $allFields = [];
        foreach ($forms as $form) {
            $allFields = array_merge($allFields, $this->getDisplayedDonorMetaFieldsForForm($form));
        }

        $customFields = [];
        foreach ($allFields as $field) {
            $value = $this->getFieldValue($field);

            if (empty($value)) {
                continue;
            }

            $customFields[] = [
                'label' => method_exists($field, 'getLabel') ? $field->getLabel() : $field->getName(),
                'value' => $value,
            ];
        }

        return $customFields;
    }

    /**
     * Get unique donation forms for the donor
     *
     * @since 4.4.0
     */
    private function getUniqueDonationFormsForDonor(): array
    {
        $donations = $this->donor->donations()->getAll();

        if (empty($donations)) {
            return [];
        }

        $uniqueFormIds = [];
        foreach ($donations as $donation) {
            $formId = $donation->formId;

            // Skip legacy forms and avoid duplicates
            if (!give(DonationFormRepository::class)->isLegacyForm($formId) && !in_array($formId, $uniqueFormIds, true)) {
                $uniqueFormIds[] = $formId;
            }
        }

        $forms = [];
        foreach ($uniqueFormIds as $formId) {
            $form = DonationForm::find($formId);
            if ($form !== null) {
                $forms[] = $form;
            }
        }

        return $forms;
    }

    /**
     * Get displayed donor meta fields for a form
     *
     * @since 4.4.0
     */
    private function getDisplayedDonorMetaFieldsForForm(DonationForm $form): array
    {
        return array_filter($form->schema()->getFields(), static function (Field $field): bool {
            return $field->shouldShowInAdmin() && $field->shouldStoreAsDonorMeta();
        });
    }

    /**
     * Get field value for a custom field
     *
     * @since 4.4.0
     */
    private function getFieldValue(Field $field): string
    {
        $metaValue = give()->donor_meta->get_meta($this->donor->id, $field->getName(), true);

        if (empty($metaValue)) {
            return '';
        }

        if ($field->getType() === Types::FILE) {
            $attachmentLink = wp_get_attachment_link($metaValue);
            return $attachmentLink ?: '';
        }

        return (string) $metaValue;
    }
}
