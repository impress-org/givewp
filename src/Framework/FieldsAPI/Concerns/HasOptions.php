<?php

namespace Give\Framework\FieldsAPI\Concerns;

use Give\Framework\FieldsAPI\Option;

trait HasOptions
{

    /** @var Option[] */
    protected $options = [];

    /**
     * Set the options
     *
     * Note that the keys of associative arrays are not supported for setting values or labels.
     * For setting labels either use `new FieldOption($value, $label)` or `[$value, $label]`.
     * In either case, the label is optional.
     *
     * @param Option|array|mixed ...$options
     *
     * @return $this
     */
    public function options(...$options)
    {
        // Reset options, since they are meant to be set immutably
        $this->options = [];

        // Loop through the options and transform them to the proper format.
        foreach ($options as $value) {
            if ($value instanceof Option) {
                // In this case, what is provided matches the proper format, so we can just append it.
                $this->options[] = $value;
            } elseif (is_array($value)) {
                // In this case, what has been provided in the value is an array with a value then a label.
                // This matches the constructor of `FieldOption`, so we can unpack it as arguments for a new instance.
                $this->options[] = new Option(...$value);
            } else {
                // In this case, we just have a value which is the bare minimum required for a `FieldOption`.
                $this->options[] = new Option($value);
            }
        }

        return $this;
    }

    /**
     * Access the options
     *
     * @return Option[]
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Check whether options exist
     *
     * @since 2.15.0
     *
     * @return bool
     */
    public function hasOptions()
    {
        return (bool)count($this->options);
    }

    /**
     * Walk through the options
     *
     * @since 2.12.0
     *
     * @param callable $callback
     *
     * @return void
     */
    public function walkOptions(callable $callback)
    {
        foreach ($this->options as $option) {
            // Call the callback for each option.
            if ($callback($option) === false) {
                // Returning false breaks the loop.
                break;
            }
        }
    }
}
