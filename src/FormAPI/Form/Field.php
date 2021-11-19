<?php

namespace Give\FormAPI\Form;

use Give\Framework\Exceptions\Primitives\InvalidArgumentException;

abstract class Field
{

    /**
     * Field id
     *
     * @since 2.7.0
     * @var string
     */
    public $id;

    /**
     * Field name
     *
     * @since 2.7.0
     * @var string
     */
    public $name;

    /**
     * Field description
     *
     * @since 2.7.0
     * @var string
     */
    public $desc = '';

    /**
     * Field type
     *
     * @since 2.7.0
     * @var string
     */
    public $type;

    /**
     * Field style
     *
     * @since 2.7.0
     * @var string
     */
    public $style = '';

    /**
     * Field wrapper class.
     *
     * @since 2.7.0
     * @var string
     */
    public $wrapperClass = '';

    /**
     * Field value.
     *
     * @since 2.7.0
     * @var string
     */
    public $value = null;

    /**
     * Field default value.
     *
     * @since 2.7.0
     * @var string
     */
    public $defaultValue = null;

    /**
     * Field attribues.
     *
     * @since 2.7.0
     * @var string
     */
    public $attributes = [];

    /**
     * Parse field arguments
     *
     * @since 2.7.0
     *
     * @param array $array
     *
     * @return mixed
     */
    public function parse($array)
    {
        $this->id = $array['id'];
        $this->name = $array['name'];
        $this->type = $array['type'];
        $this->desc = isset($array['desc']) ? $array['desc'] : '';
        $this->style = isset($array['style']) ? $array['style'] : '';
        $this->wrapperClass = isset($array['wrapper_class']) ? $array['wrapper_class'] : '';
        $this->defaultValue = isset($array['default']) ? $array['default'] : null;
        $this->value = isset($array['value']) ? $array['value'] : null;
        $this->attributes = isset($array['attributes']) ? $array['attributes'] : [];
    }

    /**
     * Get Field object.
     *
     * @since 2.7.0
     *
     * @param array $array
     *
     * @return static
     */
    public static function fromArray($array)
    {
        $field = new static();

        $field->validate($array);
        $field->parse($array);

        return $field;
    }

    /**
     * Validate field arguments
     *
     * @since 2.7.0
     *
     * @param $array
     */
    public function validate($array)
    {
        $required = ['id', 'name', 'type'];
        $array = array_filter($array); // Remove empty values.

        if (array_diff($required, array_keys($array))) {
            throw new InvalidArgumentException(
                __('To create a TextField object, please provide valid id, name and type.', 'give')
            );
        }
    }

    /**
     * Convert field object to array.
     *
     * @since 2.7.0
     * @return array
     */
    public function toArray()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'desc' => $this->desc,
            'style' => $this->style,
            'wrapper_class' => $this->wrapperClass,
            'value' => $this->value,
            'default' => $this->defaultValue,
            'attributes' => $this->attributes,
        ];
    }
}
