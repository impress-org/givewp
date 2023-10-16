<?php

namespace Give\Donations\CustomFields\Views;

use Give\Donations\Models\Donation;
use Give\Framework\FieldsAPI\Field;
use Give\Framework\FieldsAPI\Types;

/**
 * @since 3.0.0
 */
class DonationDetailsView
{
    /** @var Donation */
    protected $donation;

    /** @var Field[] */
    protected $fields;

    /**
     * @since 3.0.0
     *
     * @param  Donation  $donation
     * @param  array|Field[]  $fields
     */
    public function __construct(Donation $donation, array $fields)
    {
        $this->donation = $donation;
        $this->fields = $fields;
    }

    /**
     * @since 3.0.0
     *
     * @return string
     */
    public function render(): string
    {
        return "<div class='postbox' style='padding-bottom: 15px;'>
            <h3 class='handle'>{$this->getTitle()}</h3>
            <div class='inside'>{$this->getContents()}</div>
        </div>";
    }

    /**
     * @since 3.0.0
     *
     * @return string
     */
    protected function getTitle(): string
    {
        return __('Custom Fields', 'give');
    }

    /**
     * @since 3.0.0
     *
     * @return string
     */
    protected function getContents(): string
    {
        return array_reduce($this->fields, function ($output, Field $field) {
            $value = $this->getFieldValue($field);
            $label = method_exists($field, 'getLabel') ? $field->getLabel() : $field->getName();

            if (empty($value)) {
                return $output;
            }

            return $output . "
                <div>
                    <strong>{$label}:</strong>&nbsp;
                    {$value}
                </div>
            ";
        }, '');
    }

    /**
     * @since 3.0.0
     *
     * @param  Field  $field
     *
     * @return mixed
     */
    protected function getFieldValue(Field $field)
    {
        $metaValue = give()->payment_meta->get_meta($this->donation->id, $field->getName(), true);

        if (empty($metaValue)) {
            return '';
        }

        if ($field->getType() === Types::FILE) {
            return wp_get_attachment_link($metaValue);
        }

        return $metaValue;
    }
}
