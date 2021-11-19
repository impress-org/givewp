<?php

namespace Give\FormAPI\Form;

use Give\Framework\Exceptions\Primitives\InvalidArgumentException;

class Group extends Field
{
    /**
     * Field options.
     * Note: Allow to update repeater aka group field frontend output.
     *
     * @since 2.7.0
     * @var array
     */
    public $options = [];

    /**
     * Sub fields
     *
     * Note: Allow developer to add sub fields to group.
     *
     * @since 2.7.0
     * @var array
     */
    public $fields = [];

    /**
     * @inheritDoc
     */
    public function parse($array)
    {
        parent::parse($array);

        $defaultOptions = [
            'header_title' => esc_attr__('Group', 'give'),
            'add_button' => esc_html__('Add Row', 'give'),
            'group_numbering' => 0,
            'close_tabs' => 0,
        ];

        $this->options = isset($array['options']) ?
            array_merge($defaultOptions, $array['options']) :
            $defaultOptions;

        $this->fields = isset($array['fields']) ?
            $array['fields'] :
            [];
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        return array_merge(
            parent::toArray(),
            [
                'options' => $this->options,
                'fields' => $this->fields,
            ]
        );
    }

    /**
     * Get sub fields.
     *
     * @since 2.7.0
     *
     * @param string $fieldId
     *
     * @return array
     */
    public function getFieldArguments($fieldId)
    {
        $field = current(
            array_filter(
                $this->fields,
                static function ($field) use ($fieldId) {
                    return $fieldId === $field['id'];
                }
            )
        );

        // Validate field.
        if ( ! $field) {
            throw new InvalidArgumentException(
                sprintf(
                    __('Field with %1$s Id does not exist in group.', 'give'),
                    $fieldId
                )
            );
        }

        return $field;
    }
}
