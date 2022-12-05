<?php

namespace Give\NextGen\CustomFields\Views;

use Give\Donations\Models\Donation;
use Give\Framework\FieldsAPI\Field;

/**
 * @unreleased
 */
class DonationDetailsView
{
    /** @var Donation */
    protected $donation;

    /** @var Field[] */
    protected $fields;

    /**
     * @unreleased
     *
     * @param Donation      $donation
     * @param array|Field[] $fields
     */
    public function __construct(Donation $donation, array $fields)
    {
        $this->donation = $donation;
        $this->fields = $fields;
    }

    /**
     * @unreleased
     *
     * @return string
     */
    public function render(): string
    {
        return "
        <div class='postbox' style='padding-bottom: 15px;'>
            <h3 class='handle'>{$this->getTitle()}</h3>
            <div class='inside'>{$this->getContents()}</div>
        </div>
        ";
    }

    /**
     * @unreleased
     *
     * @return string
     */
    protected function getTitle(): string
    {
        return __('Custom Fields', 'givewp');
    }

    /**
     * @unreleased
     *
     * @return string
     */
    protected function getContents(): string
    {
        return array_reduce($this->fields, function($output, Field $field) {
            return $output . "
                <div>
                    <strong>{$field->getLabel()}:</strong>&nbsp;
                    {$this->getFieldValue($field)}
                </div>
            ";
        }, '');
    }

    /**
     * @unreleased
     *
     * @param Field $field
     *
     * @return mixed
     */
    protected function getFieldValue(Field $field)
    {
        return give()->payment_meta->get_meta($this->donation->id, $field->getName(), true );
    }
}
