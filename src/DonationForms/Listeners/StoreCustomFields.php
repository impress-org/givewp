<?php

namespace Give\DonationForms\Listeners;

use Give\DonationForms\Models\DonationForm;
use Give\Donations\Models\Donation;
use Give\Form\LegacyConsumer\Actions\UploadFilesAction;
use Give\Framework\FieldsAPI\Exceptions\NameCollisionException;
use Give\Framework\FieldsAPI\Field;
use Give\Framework\FieldsAPI\File;
use Give\Framework\FieldsAPI\Types;

class StoreCustomFields
{
    /**
     * In order to store custom fields, we need to validate them by comparing the form's
     * schema settings to the request.  Once a field has passed validation, we can determine
     * its storage location from the fields api.  This Action is designed to be triggered post-validation.
     *
     * @since 3.0.0 added support for field scopes and file uploads
     * @since 3.0.0
     *
     * @return void
     * @throws NameCollisionException
     */
    public function __invoke(DonationForm $form, Donation $donation, array $customFields)
    {
        $form->schema()->walkFields(
            function (Field $field) use ($customFields, $donation) {
                $fieldName = $field->getName();

                if (!array_key_exists($fieldName, $customFields)) {
                    return;
                }

                // File fields need to upload their file first before persisting the field based on scope.
                if ($field->getType() === Types::FILE) {
                    /** @var File $field */
                    $fileIds = $this->handleFileUpload($field);

                    if (empty($fileIds)) {
                        return;
                    }

                    foreach ($fileIds as $fileId) {
                        $this->persistFieldScope($field, $fileId, $donation);
                    }
                } else {
                    $value = $customFields[$fieldName];

                    $this->persistFieldScope($field, $value, $donation);
                }
            }
        );
    }

    /**
     * @since 3.0.0
     * @return array|null
     */
    protected function handleFileUpload(File $field)
    {
        if (!isset($_FILES[$field->getName()])) {
            return null;
        }

        return (new UploadFilesAction($field))();
    }

    /**
     * @since 3.0.0
     */
    protected function storeAsDonorMeta(int $donorId, string $metaKey, $value)
    {
        give()->donor_meta->update_meta($donorId, $metaKey, $value);
    }

    /**
     * @since 3.0.0
     */
    protected function storeAsDonationMeta(int $donationId, string $metaKey, $value)
    {
        give()->payment_meta->update_meta($donationId, $metaKey, $value);
    }

    /**
     * @since 3.0.0
     */
    protected function persistFieldScope(Field $field, $value, Donation $donation)
    {
        if ($field->getScope()->isDonor()) {
            $this->storeAsDonorMeta($donation->donorId, $field->getMetaKey() ?? $field->getName(), $value);
        } elseif ($field->getScope()->isDonation()) {
            $this->storeAsDonationMeta($donation->id, $field->getMetaKey() ?? $field->getName(), $value);
        } elseif ($field->getScope()->isCallback()) {
            $field->getScopeCallback()($field, $value, $donation);
        } else {
            do_action(
                "givewp_donation_form_persist_field_scope_{$field->getScopeValue()}",
                $field,
                $value,
                $donation
            );
        }
    }
}
