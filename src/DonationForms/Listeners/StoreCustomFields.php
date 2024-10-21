<?php

namespace Give\DonationForms\Listeners;

use Give\DonationForms\Models\DonationForm;
use Give\Donations\Models\Donation;
use Give\Form\LegacyConsumer\Actions\UploadFilesAction;
use Give\Framework\FieldsAPI\Exceptions\NameCollisionException;
use Give\Framework\FieldsAPI\Field;
use Give\Framework\FieldsAPI\File;
use Give\Framework\FieldsAPI\Types;
use Give\Subscriptions\Models\Subscription;

class StoreCustomFields
{
    /**
     * In order to store custom fields, we need to validate them by comparing the form's
     * schema settings to the request.  Once a field has passed validation, we can determine
     * its storage location from the fields api.  This Action is designed to be triggered post-validation.
     *
     * @since 3.0.0
     *
     * @return void
     * @throws NameCollisionException
     */
    public function __invoke(DonationForm $form, Donation $donation, ?Subscription $subscription, array $customFields)
    {
        $form->schema()->walkFields(
            function (Field $field) use ($customFields, $donation, $subscription) {
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
                        $this->persistFieldScope($field, $fileId, $donation, $subscription);
                    }
                } else {
                    $value = $customFields[$fieldName];

                    $this->persistFieldScope($field, $value, $donation, $subscription);
                }
            }
        );

        /**
         * Fires after custom fields have been stored/processed
         *
         * @since 3.4.0
         *
         * @param DonationForm $form
         * @param array $customFields
         * @param Donation $donation
         * @param Subscription|null $subscription
         */
        do_action('givewp_donation_form_processing_custom_fields_stored', $form, $customFields, $donation, $subscription);
    }

    /**
     * @since 3.0.0
     */
    protected function handleFileUpload(File $field): ?array
    {
        if (!isset($_FILES[$field->getName()])) {
            return null;
        }

        return (new UploadFilesAction($field))();
    }

    /**
     * @since 3.0.0
     */
    protected function storeAsDonorMeta(int $donorId, string $metaKey, $value): void
    {
        give()->donor_meta->update_meta($donorId, $metaKey, $value);
    }

    /**
     * @since 3.0.0
     */
    protected function storeAsDonationMeta(int $donationId, string $metaKey, $value): void
    {
        give()->payment_meta->update_meta($donationId, $metaKey, $value);
    }

    /**
     * @since 3.0.0
     */
    protected function storeAsSubscriptionMeta(int $subscriptionId, string $metaKey, $value): void
    {
        give()->subscription_meta->update_meta($subscriptionId, $metaKey, $value);
    }


    /**
     * @since 3.0.0
     */
    protected function persistFieldScope(Field $field, $value, Donation $donation, ?Subscription $subscription): void
    {
        if ($field->getScope()->isDonor()) {
            $this->storeAsDonorMeta($donation->donorId, $field->getMetaKey() ?? $field->getName(), $value);
        } elseif ($field->getScope()->isDonation()) {
            $this->storeAsDonationMeta($donation->id, $field->getMetaKey() ?? $field->getName(), $value);
        } elseif ($field->getScope()->isSubscription()) {
            if ($subscription) {
                $this->storeAsSubscriptionMeta($subscription->id, $field->getMetaKey() ?? $field->getName(), $value);
            }
        } elseif ($field->getScope()->isCallback()) {
            $field->getScopeCallback()($field, $value, $donation, $subscription);
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
