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
                if (isset($data[$propertyName])) {
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
     * @unreleased
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
     * @unreleased
     */
    private function getCustomFields(): array
    {
        $forms = $this->getUniqueDonationFormsForDonor();

        if (!$forms) {
            return [];
        }

        $fields = array_reduce($forms, function ($fields, DonationForm $form) {
            return $fields + $this->getDisplayedDonorMetaFieldsForForm($form);
        }, []);

        return array_reduce($fields, function($customFields, Field $field) {
            $value = $this->getFieldValue($field);
            $label = method_exists($field, 'getLabel') ? $field->getLabel() : $field->getName();

            if (empty($value)) {
                return $customFields;
            }

            $customFields[] = [
                'label' => $label,
                'value' => $value,
            ];

            return $customFields;
        }, []);
    }

    /**
     * Get unique donation forms for the donor
     *
     * @unreleased
     */
    private function getUniqueDonationFormsForDonor(): array
    {
        $formIds = array_map(static function (Donation $donation) {
            return $donation->formId;
        }, $this->donor->donations()->getAll() ?? []);

        $formIds = array_filter($formIds, static function ($formId) {
            return !give(DonationFormRepository::class)->isLegacyForm($formId);
        });

        return array_filter(array_map(static function ($formId) {
            return DonationForm::find($formId);
        }, array_unique($formIds)));
    }

    /**
     * Get displayed donor meta fields for a form
     *
     * @unreleased
     */
    private function getDisplayedDonorMetaFieldsForForm(DonationForm $form): array
    {
        return array_filter($form->schema()->getFields(), static function ($field) {
            return $field->shouldShowInAdmin() && $field->shouldStoreAsDonorMeta();
        });
    }

    /**
     * Get field value for a custom field
     *
     * @unreleased
     */
    private function getFieldValue(Field $field)
    {
        $metaValue = give()->donor_meta->get_meta($this->donor->id, $field->getName(), true);

        if (empty($metaValue)) {
            return '';
        }

        if ($field->getType() === Types::FILE) {
            return wp_get_attachment_link($metaValue);
        }

        return $metaValue;
    }
}
