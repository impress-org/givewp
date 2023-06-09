<?php

namespace Give\Form\LegacyConsumer\Commands;

use Give\Form\LegacyConsumer\Actions\UploadFilesAction;
use Give\Framework\FieldsAPI\Concenrs\StoreAsMeta;
use Give\Framework\FieldsAPI\Field;
use Give\Framework\FieldsAPI\File;
use Give\Framework\FieldsAPI\Group;
use Give\Framework\FieldsAPI\LegacyNodes\CheckboxGroup;
use Give\Framework\FieldsAPI\Text;
use Give\Framework\FieldsAPI\Types;

/**
 * Persist custom field values as donation meta.
 *
 * @since 2.10.2
 */
class SetupFieldPersistence implements HookCommandInterface
{

    /** @var int */
    private $donationId;
    /** @var array */
    private $donationData;

    /**
     * @since 2.10.2
     *
     * @param int   $donationId
     * @param array $donationData
     */
    public function __construct($donationId, $donationData)
    {
        $this->donationId = $donationId;
        $this->donationData = $donationData;
    }

    /**
     * @since 2.10.2
     *
     * @param string $hook
     *
     * @void
     */
    public function __invoke($hook)
    {
        $collection = Group::make($hook);
        do_action("give_fields_$hook", $collection, $this->donationData['give_form_id']);
        $collection->walkFields([$this, 'process']);
    }

    /**
     * Process the given field.
     *
     * @since 2.28.0 add shim for CheckboxGroup, only necessary for legacy FFM fields.
     * @since 2.10.2
     * @since 2.14.0 Handle File field type and custom field type separately. Use add meta function to persist field value.
     *
     * @param Field $field
     *
     * @return void
     */
    public function process(Field $field)
    {
        if ($field->getType() === Types::FILE) {
            /** @var File $field */
            if (isset($_FILES[$field->getName()])) {
                $fileUploader = new UploadFilesAction($field);
                $fileIds = $fileUploader();

                foreach ($fileIds as $fileId) {
                    if ($field->shouldStoreAsDonorMeta()) {
                        $donorID = give_get_payment_meta($this->donationId, '_give_payment_donor_id');
                        Give()->donor_meta->add_meta($donorID, $field->getName(), $fileId);
                    } else {
                        // Store as Donation Meta - default behavior.
                        give()->payment_meta->add_meta($this->donationId, $field->getName(), $fileId);
                    }
                }
            }
        } elseif (in_array($field->getType(), Types::all(), true) || $field->getType() === CheckboxGroup::TYPE) {
            if (isset($_POST[$field->getName()])) {
                $data = give_clean($_POST[$field->getName()]);
                $value = is_array($data) ?
                    implode('| ', array_values(array_filter($data))) :
                    $data;

                /** @var Text $field */
                if ($field->shouldStoreAsDonorMeta()) {
                    $donorID = give_get_payment_meta($this->donationId, '_give_payment_donor_id');
                    Give()->donor_meta->add_meta($donorID, $field->getName(), $value);
                } else {
                    // Store as Donation Meta - default behavior.
                    give()->payment_meta->add_meta($this->donationId, $field->getName(), $value);
                }
            }
        } else {
            /**
             * Use this action to save custom field which does not exist in fields API.
             *
             * @since 2.14.0
             *
             * @param Field $field
             * @param int   $donationId
             * @param array $donationData
             */
            do_action('give_fields_api_save_field', $field, $this->donationId, $this->donationData);
        }
    }
}
