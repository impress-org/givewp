<?php

namespace Give\NextGen\DonationForm\Actions;

use Give\Donations\Models\Donation;
use Give\Framework\FieldsAPI\Field;
use Give\NextGen\DonationForm\Models\DonationForm;

class StoreCustomFields
{
    /**
     * In order to store custom fields, we need to validate them by comparing the form's
     * schema settings to the request.  Once a field has passed validation, we can determine
     * its storage location from the fields api.  This Action is designed to be triggered post-validation.
     *
     * @since 0.1.0
     *
     * @return void
     */
    public function __invoke(DonationForm $form, Donation $donation, array $customFields)
    {
        $form->schema()->walkFields(
            static function (Field $field) use ($customFields, $donation) {
                if (!array_key_exists($field->getName(), $customFields)) {
                    return;
                }

                $value = $customFields[$field->getName()];

                if ($field->shouldStoreAsDonorMeta()) {
                    // save as donor meta
                    give()->donor_meta->update_meta($donation->donorId, $field->getName(), $value);
                } else {
                    // save as donation meta
                    give()->payment_meta->update_meta($donation->id, $field->getName(), $value);
                }
            }
        );
    }
}
