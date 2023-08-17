<?php

namespace Give\Donors\CustomFields\Views;

use Give\Donors\Models\Donor;
use Give\Framework\FieldsAPI\Field;
use Give\Framework\FieldsAPI\Types;

/**
 * @since 3.0.0
 */
class DonorDetailsView
{
    /** @var Donor */
    protected $donor;

    /** @var Field[] */
    protected $fields;

    /**
     * @since 3.0.0
     *
     * @param  Donor  $donor
     * @param  Field[]  $fields
     */
    public function __construct(Donor $donor, array $fields)
    {
        $this->donor = $donor;
        $this->fields = $fields;
    }

    /**
     * @since 3.0.0
     *
     * @return string
     */
    public function render(): string
    {
        return "<h3>{$this->getTitle()}</h3>
        <table class='wp-list-table widefat striped donations'>
			<thead>
                <tr>
                    <th scope='col'>Field</th>
                    <th scope='col'>Value</th>
                </tr>
			</thead>
			<tbody>
			    {$this->getTableRows()}
            </tbody>
		</table>";
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
    protected function getTableRows(): string
    {
        return array_reduce($this->fields, function($output, Field $field) {
            $value = $this->getFieldValue($field);
            $label = method_exists($field, 'getLabel') ? $field->getLabel() : $field->getName();

            if (empty($value)) {
                return $output;
            }

            return $output . "
                <tr>
                    <td>{$label}</td>
                    <td>{$value}</td>
                </tr>
            ";
        }, '');
    }

    /**
     * @since 3.0.0
     *
     * @return mixed
     */
    protected function getFieldValue(Field $field)
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
