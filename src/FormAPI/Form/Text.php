<?php

namespace Give\FormAPI\Form;

class Text extends Field
{
    /**
     * Before field.
     *
     * @since 2.7.0
     * @var string
     */
    public $beforeField = '';

    /**
     * after field.
     *
     * @since 2.7.0
     * @var string
     */
    public $afterField = '';

    /**
     * Field value type.
     * Note: this param value can be price and decimal.
     *
     * @since 2.7.0
     * @var string
     */
    public $dataType = '';

    /**
     * Field value type.
     * Note: this param value can be price and decimal.
     *
     * @since 2.7.0
     * @var string
     */
    public $size = '';

    /**
     * @inheritDoc
     */
    public function parse($array)
    {
        parent::parse($array);

        $this->beforeField = isset($array['before_field']) ? $array['before_field'] : '';
        $this->afterField = isset($array['after_field']) ? $array['after_field'] : '';
        $this->dataType = isset($array['data_type']) ? $array['data_type'] : '';

        $type = explode('_', $this->type, 2);
        $this->size = false !== strpos('_', $this->type) ? array_pop($type) : '';
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        return array_merge(
            parent::toArray(),
            [
                'before_field' => $this->beforeField,
                'after_field' => $this->afterField,
            ]
        );
    }
}
