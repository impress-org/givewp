<?php

namespace Give\Form\LegacyConsumer\Commands;

use Give\Framework\FieldsAPI\Field;
use Give\Framework\FieldsAPI\Group;
use Give\Receipt\DonationReceipt;

/**
 * @since 2.10.2
 */
class SetupFieldReceipt
{

    /**
     * @since 2.10.2
     *
     * @param DonationReceipt $receipt
     */
    public function __construct(DonationReceipt $receipt)
    {
        $this->receipt = $receipt;
    }

    /**
     * @since 2.10.2
     *
     * @param string $hook
     *
     * @return void
     */
    public function __invoke($hook)
    {
        $formID = give_get_payment_meta($this->receipt->donationId, '_give_payment_form_id');

        $collection = Group::make($hook);
        do_action("give_fields_{$hook}", $collection, $formID);

        $collection->walkFields([$this, 'apply']);
    }

    /**
     * @since 2.10.2
     *
     * @param Field $field
     *
     * @return void
     */
    public function apply(Field $field)
    {
        if ( ! $field->shouldShowInReceipt()) {
            return;
        }

        if ($field->shouldStoreAsDonorMeta()) {
            $this->addDonorLineItem($field);
        } else {
            $this->addAdditionalLineItems($field);
        }
    }

    /**
     * @since 2.10.2
     *
     * @param Field $field
     *
     * @return void
     */
    protected function addDonorLineItem(Field $field)
    {
        $donorID = give_get_payment_meta($this->receipt->donationId, '_give_payment_donor_id');
        if ($value = Give()->donor_meta->get_meta($donorID, $field->getName(), true)) {
            $this->receipt
                ->getSections()[DonationReceipt::DONORSECTIONID]
                ->addLineItem(
                    [
                        'id' => $field->getName(),
                        'label' => $field->getLabel(),
                        'value' => $value,
                    ]
                );
        }
    }

    /**
     * @since 2.10.2
     *
     * @param Field $field
     *
     * @return void
     */
    protected function addAdditionalLineItems(Field $field)
    {
        if ($value = give_get_payment_meta($this->receipt->donationId, $field->getName())) {
            $this->receipt
                ->getSections()[DonationReceipt::ADDITIONALINFORMATIONSECTIONID]
                ->addLineItem(
                    [
                        'id' => $field->getName(),
                        'label' => $field->getLabel(),
                        'value' => $value,
                    ]
                );
        }
    }
}
